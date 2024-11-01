<?php
/**
 * Represents the view for the login form.
 *
 * @package   Spiral_v2_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$error_message = __('Log in Error', 'spiral-member-login');

if (isset($_REQUEST["message"])) {
	switch ($_REQUEST["message"]) {
		case "unauthorized":
			$error_message = __('Log in Error', 'spiral-member-login');
			break;
		default:
			$error_message = __('Log in Error', 'spiral-member-login');
	}
}
$login_label_default_text_en = esc_html(get_option('spiral_member_login')['login_id_label_en']);
$login_label_default_text_jp = esc_html(get_option('spiral_member_login')['login_id_label_jp']);
?>

<div class="sml-login" id="spiral-member-login<?php echo esc_attr($template->the_template_num()); ?>">
	<?php if (isset($_REQUEST["message"])) : ?>
		<p class="error sml-login-error-message"><?php echo esc_html($error_message); ?><br></p>
	<?php endif; ?>
	<form name="loginform" class="wpmls_login_form" id="loginform<?php echo esc_attr($template->the_template_num()); ?>" action="<?php echo esc_attr($template->the_auth_form_url()); ?>" method="POST">
		<p>
			<label for="sml-label-user-login">
				<?php echo esc_html(get_locale() == 'en_US' ? $login_label_default_text_en : $login_label_default_text_jp); ?>
			</label>
			<input required type="text" class="login_id" name="login_id" id="user_login<?php echo esc_attr($template->the_template_num()); ?>" class="input" value="<?php echo esc_attr($template->the_posted_value('login_id')); ?>" size="20" />
		</p>
		<p>
			<label for="sml-label-user-pass"><?php esc_html_e('Password','spiral-member-login'); ?></label>
			<input required type="password" class="password" name="password" id="user_pass<?php echo esc_attr($template->the_template_num()); ?>" class="input" value="" size="20" />
		</p>
		<p class="submit">
			<?php wp_nonce_field( 'wpmls_login_action', '_nonce' ); ?>
			<input type="hidden" name="site_id" value="<?php echo esc_attr(get_option('spiral_member_login')['site_id']); ?>" />
			<input type="hidden" name="authentication_id" value="<?php echo esc_attr(get_option('spiral_member_login')['authentication_id']); ?>" />
			<button class="sml-login-submit log-in-btn" type="submit" name="wp-submit" id="wp-submit<?php echo esc_attr($template->the_template_num()); ?>"><span><?php esc_html_e('Log in','spiral-member-login'); ?></span></button>
			<input type="hidden" name="template_num" value="<?php echo esc_attr($template->the_template_num()); ?>" />
			<input type="hidden" name="wp_site" value="<?php echo esc_url($template->full_site_url()); ?>" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_url($template->the_redirect_url()); ?>" />
			<input type="hidden" name="action" value="login" />
		</p>
	</form>
</div>