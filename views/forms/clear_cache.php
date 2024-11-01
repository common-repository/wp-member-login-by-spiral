<?php 
if (!defined('ABSPATH')) exit;
?>
<form class="wpmls_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" name="frmSetting" method="post">
    <?php wp_nonce_field('wpmls_clear_cache_nonce', 'wpmls_clear_cache_nonce'); ?>
    <input type="hidden" name="action" value="clear_cache_action" />
    <input type="hidden" name="form_name" value="wpmls_clear_cache_form">
    <h2><?php esc_html_e('Clear login and API caches','spiral-member-login') ?></h2>
    <button name="clear_cache_db" class="button button-primary clear_cache" type="submit">
        <?php esc_html_e('Clear','spiral-member-login') ?>
    </button>
</form>