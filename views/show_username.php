<?php
/**
 * Represents the view for the user info.
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="sml-login" id="spiral-member-login<?php esc_attr($template->the_template_num()); ?>">
	<?php $template->the_user_links($atts); ?>
	<?php do_action( 'sml_user_info' ); ?>
</div>