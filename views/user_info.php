<?php
/**
 * Represents the view for the user info.
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$atts = [
	'is_target_blank' => $template->options['is_target_blank'],
	'show_logout_link' => $template->options['show_logout_link']
];
?>
<div class="sml-login" id="spiral-member-login<?php esc_attr($template->the_template_num()); ?>">
	<?php $template->the_user_name(); ?>
	<?php $template->the_user_links($atts); ?>
	<?php do_action( 'sml_user_info' ); ?>
</div>