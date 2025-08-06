<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * SSO SiberMu Authentication Plugin.
 *
 * @package    auth_sso_sibermu
 * @copyright  2025 Joko Supriyanto (joko@sibermu.ac.id)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

/**
 * SSO SiberMu authentication plugin class.
 *
 * @package    auth_sso_sibermu
 */
class auth_plugin_sso_sibermu extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'sso_sibermu';
        $this->config = get_config('auth/sso_sibermu');
    }

    /**
     * Hook to add the SSO button to the login page.
     *
     * @return void
     */
    public function loginpage_hook() {
        global $OUTPUT, $DB;

        $loginUrl = new moodle_url('/auth/sso_sibermu/login.php', ['sesskey' => sesskey()]);
        $buttonTextFromDb = $DB->get_field('config_plugins', 'value', ['plugin' => 'auth_sso_sibermu', 'name' => 'login_button_text']);

        if (!empty(trim($buttonTextFromDb))) {
            $buttonText = $buttonTextFromDb;
        } else {
            $buttonText = 'Login with SSO SiberMu';
        }

 // 1. Dapatkan URL logo dari direktori /pix/ plugin.
        // Ganti 'logo_sso' dengan nama file PNG Anda tanpa ekstensi .png
        $logoUrl = (new moodle_url('/auth/sso_sibermu/pix/logo_sso.png'))->out(false);

        // 2. Buat HTML untuk tombol yang berisi logo dan teks.
        $buttonHtmlName = '<img src="' . $logoUrl . '" alt="SSO Logo" style="height: 30px; vertical-align: middle; margin-right: 8px;"> ' . $buttonText;

        // 3. Siapkan data untuk template dengan HTML baru dan kosongkan iconurl.
        $templateData = [
            'idps' => [[
                'name' => $buttonHtmlName, // Menggunakan HTML yang sudah ada logonya
                'url' => $loginUrl->out(false),
                'iconurl' => '', // Dikosongkan karena kita sudah pakai logo sendiri
            ]]
        ];

        // 1. Get the placement selector value directly from the database.
        $selectorFromDb = $DB->get_field(
            'config_plugins',
            'value',
            [
                'plugin' => 'auth_sso_sibermu',
                'name'   => 'button_placement_selector'
            ]
        );

        // 2. Use the value from the database. If empty, use '.loginform' as the default.
        $placementSelector = !empty(trim($selectorFromDb)) ? $selectorFromDb : '.loginform';

        // 3. Encode the selector for security when inserting into JavaScript.
        $jsSelector = json_encode($placementSelector);

        // Render the SSO button, wrapped in a div for easy discovery.
        echo '<div id="sso_sibermu_button_container" style="display: none;">';
        echo $OUTPUT->render_from_template('auth_sso_sibermu/button', $templateData);
        echo '</div>';

        // 4. Pass the selector variable from PHP into JavaScript.
        echo <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ssoButtonContainer = document.getElementById('sso_sibermu_button_container');

        // Use the custom selector from the plugin settings.
        const oauthProvidersContainer = document.querySelector({$jsSelector});

        if (ssoButtonContainer && oauthProvidersContainer) {
            oauthProvidersContainer.appendChild(ssoButtonContainer);
            ssoButtonContainer.style.display = 'block';
        } else if (ssoButtonContainer) {
            // If the selector is not found, display the button in its default position (at the top).
            ssoButtonContainer.style.display = 'block';
        }
    });
</script>
HTML;
    }

    /**
     * Handles the user logout process for Single Log-Out (SLO).
     *
     * @return void
     */
    public function user_logout() {
        global $CFG;

        // 1. End the Moodle session first. This is a crucial step.
        require_logout();

        // 2. After the Moodle session has ended, redirect to the SSO server for Single Log-Out.
        if (empty($this->config->sso_server_url)) {
            // If the SSO logout URL is not set, the process ends here.
            return;
        }

        $logoutUrl = new moodle_url(
            $this->config->sso_server_url . '/logout.php',
            ['redirect_uri' => (string)new moodle_url('/login/index.php')]
        );

        redirect($logoutUrl);
    }

   /**
     * Synchronizes user data from SSO with Moodle.
     * If the user doesn't exist, it creates a new user based on SSO data.
     *
     * @param  \stdClass $ssoUser The user data object from the SSO server.
     * @return \stdClass|false The Moodle user object if found or created, otherwise false.
     */
   public function sync_user_data(\stdClass $ssoUser) {
        global $DB, $CFG;

        // 1. Validasi data dari SSO.
        if (empty($ssoUser->nisn)) {
            return false;
        }
        $ssoUsername = trim($ssoUser->nisn);

        // 2. Cari pengguna di Moodle.
        $user = $DB->get_record('user', [
            'username' => $ssoUsername,
            'deleted'  => 0,
        ]);

        // 3. Jika pengguna ditemukan, langsung login.
        if ($user) {
            return $user;
        } else {
            // PERBAIKAN: Logika pengecekan yang lebih kuat.
            // Pengguna tidak ditemukan. Periksa kebijakan pembuatan pengguna dari database.
            $usercreationpolicy = $DB->get_field(
                'config_plugins',
                'value',
                ['plugin' => 'auth_sso_sibermu', 'name' => 'user_creation_policy'],
                IGNORE_MISSING // Jangan error jika record tidak ada, kembalikan null.
            );

            // Nilai dari database adalah string ('0' atau '1'). Jika pengaturan belum pernah disimpan,
            // $usercreationpolicy akan menjadi null. Kita hanya akan membuat user jika nilainya adalah string '1'.
            if ($usercreationpolicy === '1') {
                // Kebijakan mengizinkan, buat pengguna baru.
                $newuser = new \stdClass();
                $newuser->username = $ssoUsername;

                if (filter_var($ssoUsername, FILTER_VALIDATE_EMAIL)) {
                    $newuser->email = $ssoUsername;
                } else {
                    $newuser->email = $ssoUsername . '@email.dummy.sibermu.ac.id';
                }

                $newuser->auth      = $this->authtype;
                $newuser->firstname = !empty($ssoUser->nama) ? $ssoUser->nama : 'Pengguna';
                $newuser->lastname  = 'SSO SiberMu';

                $newuser->mnethostid   = $CFG->mnet_localhost_id;
                $newuser->confirmed    = 1;
                $newuser->policyagreed = 1;
                $newuser->password     = 'not-used';
                $newuser->lang         = 'id';
                $newuser->city         = 'Kota';
                $newuser->country      = 'ID';

                $newuserid = $DB->insert_record('user', $newuser);
                return $DB->get_record('user', ['id' => $newuserid]);
            } else {
                // Jika kebijakan adalah '0', null (belum diset), atau nilai lainnya, gagalkan login.
                return false;
            }
        }
    }
}