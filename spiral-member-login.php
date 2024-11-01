<?php

/**
 * @package   Spiral_Member_Login
 * @author    SPIRAL Inc.
 * @license   GPLv2
 * @link      https://www.spiral-platform.co.jp/
 * @copyright (c) SPIRAL Inc.
 * @copyright Portions copyright (c) Eric Mann
 *
 * @wordpress-plugin
 * Plugin Name: WP Member Login by SPIRAL
 * Description: Add membership management and secure authentication by SPIRAL&reg; into your WordPress site.
 * Version:     1.2.6
 * Author:      SPIRAL Inc.
 * Author URI:  https://www.spiral-platform.co.jp/
 * License:     GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: spiral-member-login
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}
// Check Existign Setting
if(get_option('sml_version')){
	update_option("version",get_option('sml_version'));
	delete_option("sml_version");
}
if(get_option('sml_is_setup')){
	update_option("is_setup",get_option('sml_is_setup'));
	delete_option("sml_is_setup");
}

$current_option_data = get_option("spiral_v2_member_login");

if ($current_option_data !== false) {
    $new_option_name = 'spiral_member_login';
    update_option($new_option_name, $current_option_data);
	delete_option("spiral_v2_member_login");
}
// Setup
define('WPMLS_VERSION', get_option('version'));
define('WPMLS_PLUGIN_URL', __FILE__);
define('WPMLS_PLUGIN_DIR_PATH',plugin_dir_path(WPMLS_PLUGIN_URL));
define('WPMLS_CUSTOM_OPTIONS',WP_CONTENT_URL.'/plugins/wp-member-login-by-spiral/views/forms/custom_options.php');
define('WPMPS_ADMIN_ABSPATH', ABSPATH.'/wp-admin/admin.php');


if (get_option('version') == false) {
	add_option('version', 1);
	add_option('is_setup', true);
}

require_once(WPMLS_PLUGIN_DIR_PATH . 'include/class-spiral-member-login-base.php');
require_once(WPMLS_PLUGIN_DIR_PATH . 'include/class-spiral-member-login-widget.php');
require_once(WPMLS_PLUGIN_DIR_PATH . 'include/class-spiral-member-login-session.php');
require_once(WPMLS_PLUGIN_DIR_PATH . 'include/class-spiral-member-login-template.php');
require_once(WPMLS_PLUGIN_DIR_PATH . 'libs/class-password-hash.php');

if (get_option('version') == 1) {
	require_once(WPMLS_PLUGIN_DIR_PATH . 'version_one/class-spiral-api.php');
	require_once(WPMLS_PLUGIN_DIR_PATH . 'version_one/class-spiral-member-login.php');
	require_once(WPMLS_PLUGIN_DIR_PATH . 'custom_blocks/version_one/enqueue.php');
	add_action('enqueue_block_editor_assets', 'wpmls_enqueue_block_editor_assets');
	add_action('enqueue_block_assets', 'wpmls_enqueue_block_assets');
}else if((get_option('version') == 2)){
	require_once(WPMLS_PLUGIN_DIR_PATH . 'version_two/class-spiral-platform-api.php');
	require_once(WPMLS_PLUGIN_DIR_PATH . 'version_two/class-spiral-v2-member-login.php');
	require_once(WPMLS_PLUGIN_DIR_PATH . 'custom_blocks/version_two/enqueue.php');
	add_action('enqueue_block_editor_assets', 'wpmls_enqueue_block_editor_assets');
	add_action('enqueue_block_assets', 'wpmls_enqueue_block_assets');
}else{
	die;
}
register_uninstall_hook(WPMLS_PLUGIN_URL, array('WPMLS_Spiral_Member_Login', 'uninstall'));
register_activation_hook(WPMLS_PLUGIN_URL, array('WPMLS_Spiral_Member_Login', 'activate'));
register_deactivation_hook(WPMLS_PLUGIN_URL, array('WPMLS_Spiral_Member_Login', 'deactivate'));

WPMLS_Spiral_Member_Login::get_instance();

add_action('admin_enqueue_scripts', function (string $hookSuffix) {
	if ($hookSuffix === 'widgets.php') {
		wp_dequeue_script('wp-editor');
	}
}, 10, 1);
