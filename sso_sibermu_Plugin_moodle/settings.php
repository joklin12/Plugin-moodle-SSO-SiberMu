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
 * SSO SiberMu Authentication Plugin Settings.
 *
 * @package    auth_sso_sibermu
 * @copyright  2025 Joko Supriyanto (joko@sibermu.ac.id)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Definisikan nilai untuk opsi agar mudah dibaca
define('USER_CREATION_NO_INSERT', 0);
define('USER_CREATION_INSERT', 1);

// Existing settings.
$settings->add(new admin_setting_configtext(
    'auth_sso_sibermu/sso_server_url',
    get_string('sso_server_url', 'auth_sso_sibermu'),
    get_string('sso_server_url_desc', 'auth_sso_sibermu') .
    '<br>' .
    get_string('callback_url_info', 'auth_sso_sibermu', $CFG->wwwroot),
    '',
    PARAM_URL
));

$settings->add(new admin_setting_configtext(
    'auth_sso_sibermu/api_token',
    get_string('api_token', 'auth_sso_sibermu'),
    get_string('api_token_desc', 'auth_sso_sibermu'),
    '',
    PARAM_ALPHANUMEXT
));

$settings->add(new admin_setting_configtext(
    'auth_sso_sibermu/login_button_text',
    get_string('login_button_text', 'auth_sso_sibermu'),
    get_string('login_button_text_desc', 'auth_sso_sibermu'),
    'Login with SSO SiberMu',
    PARAM_TEXT
));

// BARU: Pengaturan dropdown untuk kebijakan pembuatan pengguna.
$settings->add(new admin_setting_configselect(
    'auth_sso_sibermu/user_creation_policy',
    get_string('user_creation_policy', 'auth_sso_sibermu'),
    get_string('user_creation_policy_desc', 'auth_sso_sibermu'),
    USER_CREATION_NO_INSERT, // Nilai default: jangan buat pengguna.
    [
        USER_CREATION_NO_INSERT => get_string('user_creation_policy_no_insert', 'auth_sso_sibermu'),
        USER_CREATION_INSERT    => get_string('user_creation_policy_insert', 'auth_sso_sibermu'),
    ]
));

$settings->add(new admin_setting_heading(
    'auth_sso_sibermu/advanced_settings_heading',
    get_string('advanced_settings_heading', 'auth_sso_sibermu'),
    ''
));

$settings->add(new admin_setting_configtext(
    'auth_sso_sibermu/button_placement_selector',
    get_string('button_placement_selector', 'auth_sso_sibermu'),
    get_string('button_placement_selector_desc', 'auth_sso_sibermu'),
    '.loginform', // Default value.
    PARAM_TEXT
));

// New settings for institution & organization.
$settings->add(new admin_setting_heading(
    'auth_sso_sibermu/user_restrictions',
    get_string('user_restrictions', 'auth_sso_sibermu'),
    get_string('user_restrictions_desc', 'auth_sso_sibermu')
));

$settings->add(new admin_setting_configtext(
    'auth_sso_sibermu/allowed_institution',
    get_string('allowed_institution', 'auth_sso_sibermu'),
    get_string('allowed_institution_desc', 'auth_sso_sibermu'),
    '',
    PARAM_TEXT
));

$settings->add(new admin_setting_configtext(
    'auth_sso_sibermu/allowed_organization',
    get_string('allowed_organization', 'auth_sso_sibermu'),
    get_string('allowed_organization_desc', 'auth_sso_sibermu'),
    '',
    PARAM_TEXT
));