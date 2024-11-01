<?php
if (!defined('ABSPATH')) exit;
?>
<form class="wpmls_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="clear-spiral-setting-form" name="clearSettingForm" method="POST">
    <?php wp_nonce_field('wpmls_clear_setting_nonce', 'wpmls_clear_setting_nonce'); ?>
    <input type="hidden" name="action" value="clear_setting_action" />
    <input type="hidden" name="form_name" value="wpmls_clear_setting_form">
    <h2><?php esc_html_e('Clear all SPIRAL settings', 'spiral-member-login') ?></h2>
    <button name="btn_clear_spiral_setting" class="button button-primary clear_spiral_setting" type="submit"><?php esc_html_e('Clear', 'spiral-member-login') ?></button>
</form>