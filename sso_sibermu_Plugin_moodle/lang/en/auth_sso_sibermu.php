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
 * SSO SiberMu Authentication Plugin strings.
 *
 * @package    auth_sso_sibermu
 * @copyright  2025 Joko Supriyanto (joko@sibermu.ac.id)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced_settings_heading'] = 'Advanced Settings';
$string['allowed_institution'] = 'Allowed Institutions';
$string['allowed_institution_desc'] = 'Only allow login from specific institutions. Example: <strong>SIBERMU</strong>. Leave empty to allow all institutions.';
$string['allowed_organization'] = 'Allowed Organization';
$string['allowed_organization_desc'] = 'Enter the exact name of the organization allowed to log in. Example: <strong>Muhammadiyah</strong>. Leave empty to allow any organization.';
$string['api_token'] = 'Application API Token';
$string['api_token_desc'] = 'The unique API token provided by the SSO server for this Moodle application. Taken from: sso.sibermu.ac.id.';
$string['auth_sso_sibermudescription'] = 'Authentication plugin to connect with SiberMu SSO Server.';
$string['button_placement_selector'] = 'CSS Button Placement';
$string['button_placement_selector_desc'] = '<strong>Settings for developers.</strong> Enter the CSS selector of the container element on the login page where the SSO button will be moved. Use the format .class or #id. If incorrect, the button may not appear or move. (example : .login-form | .login-identityproviders | .up)';
$string['callback_url_info'] = 'URL to copy to SiberMu SSO application: {$a}/auth/sso_sibermu/callback.php';
$string['error_auth_failed'] = 'Authentication process failed or was canceled. Please try again.';
$string['error_org_mismatch'] = 'Login Failed. Your organization is not allowed to access this application.';
$string['error_sso_verification'] = 'Failed to verify your session with the SSO server.';
$string['errornosettings'] = 'SSO plugin settings are incomplete. Please contact the administrator.';
$string['login_button_text'] = 'Login Button Text';
$string['login_button_text_desc'] = 'The text to be displayed on the SSO login button.';
$string['pluginname'] = 'SSO SiberMu';
$string['sso_server_url'] = 'SSO Server URL';
$string['sso_server_url_desc'] = 'The root URL of your SSO server (example: https://sso.sibermu.ac.id/).';
$string['user_restrictions'] = 'User Restriction (Optional)';
$string['user_restrictions_desc'] = 'If both fields below are left empty, all users from the SSO can log in. If one or both fields are filled, users must match the specified criteria.';

// BARU: String untuk pengaturan pembuatan pengguna
$string['user_creation_policy'] = 'User Creation Policy';
$string['user_creation_policy_desc'] = 'Choose whether to automatically create a new user account in Moodle if the user exists in SSO but not in Moodle.';
$string['user_creation_policy_no_insert'] = 'Don\'t insert data from SSO SiberMu (Default)';
$string['user_creation_policy_insert'] = 'Insert data from SSO SiberMu';