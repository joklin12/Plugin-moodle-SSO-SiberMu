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
 * SSO SiberMu callback handler.
 *
 * @package    auth_sso_sibermu
 * @copyright  2025 Joko Supriyanto (joko@sibermu.ac.id)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This is an external entry point, so we don't do require_login().
// phpcs:ignore moodle.spec.requires.login
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/authlib.php');

/**
 * Helper function for clean logging.
 *
 * @copyright  2025 Joko Supriyanto (joko@sibermu.ac.id)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param string $stepname The name of the step being logged.
 * @param object $userobj The user object to log.
 * @param object $sessionobj The session object to log.
 * @return void
 */
function sso_log_session_state(string $stepname, $userobj, $sessionobj) {
    global $CFG;
    $logFile = $CFG->dataroot . '/sso_debug_log.txt';
    $logEntry = "===== STEP: {$stepname} | " . date('Y-m-d H:i:s') . " =====\n";
    $logEntry .= "--- Current USER object ---\n";
    $logEntry .= var_export($userobj, true);
    $logEntry .= "\n--- Current SESSION object ---\n";
    $logEntry .= var_export($sessionobj, true);
    $logEntry .= "\n\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Delete old log if present to start a new debug session.
@unlink($CFG->dataroot . '/sso_debug_log.txt');

// ==== FORENSICS START ====

// LOG 1: Immediately after Moodle starts. What is the initial session state?
sso_log_session_state('1. AFTER MOODLE BOOTSTRAP', $USER, $SESSION);

// Attempt to clean the user session.
unset($SESSION->USER);

// LOG 2: Immediately after we try to clean the session. Did it work?
sso_log_session_state('2. AFTER UNSET $SESSION->USER', $USER, $SESSION);

try {
    // ... (rest of the callback logic as usual) ...
    $code = optional_param('code', null, PARAM_RAW);
    if (is_null($code)) {
        throw new Exception("Parameter 'code' not received from SSO server.");
    }
    $config = get_config('auth_sso_sibermu');
    $authplugin = get_auth_plugin('sso_sibermu');
    if (!$authplugin || empty($config->sso_server_url)) {
        throw new moodle_exception('errornosettings', 'auth_sso_sibermu');
    }
    $verifyUrl = $config->sso_server_url . '/api/verify_code.php';
    $postData = ['code' => $code];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($response);
    if ($httpCode !== 200 || !isset($data->success) || !$data->success) {
        throw new Exception("Verification with SSO server failed.");
    }
    $ssoUserData = $data->user;
    $allowedInstitution = !empty($config->allowed_institution) ? trim($config->allowed_institution) : '';
    $allowedOrganization = !empty($config->allowed_organization) ? trim($config->allowed_organization) : '';
    if (!empty($allowedInstitution)) {
        if (empty($ssoUserData->institusi) || strcasecmp($ssoUserData->institusi, $allowedInstitution) !== 0) {
            throw new Exception("Login rejected: User's institution is not allowed.");
        }
    }
    if (!empty($allowedOrganization)) {
        if (empty($ssoUserData->organisasi) || strcasecmp($ssoUserData->organisasi, $allowedOrganization) !== 0) {
            throw new Exception("Login rejected: User's organization is not allowed.");
        }
    }
    $moodleUser = $authplugin->sync_user_data($ssoUserData);
    if (!$moodleUser) {
        throw new Exception("Failed to sync user data to Moodle. Ensure the user's email exists in Moodle.");
    }

    // LOG 3: Just before we call complete_user_login.
    // This is the decisive moment. Does the Admin session reappear?
    sso_log_session_state('3. BEFORE COMPLETE_USER_LOGIN', $USER, $SESSION);

    complete_user_login($moodleUser);

    // Key fix: Never use the problematic $SESSION->wantsurl.
    // Redirect directly to the user's dashboard after a successful login.
    // This is the safest and most stable flow.
    $destination = new moodle_url('/my/');

    // Clean up old session variables for hygiene (optional but good practice).
    if (isset($SESSION->wantsurl)) {
        unset($SESSION->wantsurl);
    }

    redirect($destination);

} catch (Exception $e) {
    // LOG 4: If an error occurs, record the state at that moment.
    sso_log_session_state('4. ON EXCEPTION: ' . $e->getMessage(), $USER, $SESSION);

    // Store the user-friendly error message in the session.
    // Moodle's login page will automatically display this message.
    $SESSION->loginerrormsg = $e->getMessage();

    // Redirect back to the login page so the user can see the error.
    $loginUrl = new moodle_url('/login/index.php');
    redirect($loginUrl);
}