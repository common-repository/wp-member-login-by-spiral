<?php
if (!defined('ABSPATH')) exit;
$is_target_blank = ['is_target_blank' => $template->options['is_target_blank']];
switch (WPMLS_VERSION) {
    case WPMLS_VERSION == 2:
        include(plugin_dir_path(__DIR__) . 'views/forms/login_v2.php');
        break;
    default:
        include(plugin_dir_path(__DIR__) . 'views/forms/login_v1.php');
        break;
}
$template->the_action_links($is_target_blank);