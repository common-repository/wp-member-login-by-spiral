<?php

/**
 * Represents the view for the login form.
 *
 * @package   spiral_member_login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
$is_taget_blank = ["is_target_blank" => $this->get_option('enable_blank_tab')];
switch (WPMLS_VERSION) {
	case WPMLS_VERSION == 2:
		include(plugin_dir_path(__DIR__) . 'views/forms/login_v2.php');
		break;
	default:
		include(plugin_dir_path(__DIR__) . 'views/forms/login_v1.php');
		break;
}
$template->the_action_links($is_taget_blank);
