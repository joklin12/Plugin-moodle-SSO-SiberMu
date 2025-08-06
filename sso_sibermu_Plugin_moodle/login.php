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
 * SSO SiberMu Login initiator.
 *
 * @package    auth_sso_sibermu
 * @copyright  2025 Joko Supriyanto (joko@sibermu.ac.id)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This is an external entry point for guests, so we don't do require_login().
// phpcs:ignore moodle.spec.requires.login
require_once(__DIR__ . '/../../config.php');

global $SESSION, $CFG;
$config = get_config('auth_sso_sibermu');

if (empty($config->sso_server_url) || empty($config->api_token)) {
    throw new moodle_exception('errornosettings', 'auth_sso_sibermu');
}

$SESSION->wantsurl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $CFG->wwwroot;
$callbackUrl = new moodle_url('/auth/sso_sibermu/callback.php');
$authUrl = $config->sso_server_url . '/auth.php?' . http_build_query([
    'redirect_uri' => $callbackUrl->out(false),
    'token'        => $config->api_token,
]);

redirect($authUrl);