<?php

// This file contains the form for basic settings
?>

<form id="setting-form" name="frmSetting" method="post" action="<?php esc_attr_e(str_replace("http://", "https://", WPMLS_CUSTOM_OPTIONS)) ?>">
  <?php wp_nonce_field('wpmls_save_setting_nonce', 'wpmls_save_setting_nonce'); ?>
  <?php settings_fields($this->options_key); ?>
  <?php do_settings_sections($this->options_key); ?>
  <?php submit_button(); ?>
</form>