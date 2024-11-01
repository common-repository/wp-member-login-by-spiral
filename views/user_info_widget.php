<?php

/**
 * Represents the view for the user info.
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
$is_target_blank = ['is_target_blank' => $this->get_option('enable_blank_tab')];
?>
<div class="sml-login" id="spiral-member-login<?php esc_attr($template->the_template_num()); ?>">
	<?php $template->the_user_name(); ?>
	<?php
	$template->the_user_links($is_target_blank);
	?>
	<?php do_action('sml_user_info'); ?>
</div>