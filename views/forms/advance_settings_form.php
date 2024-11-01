<?php
// This file contains the form for advanced settings
?>

<form id="setting-form" name="frmSetting" method="post" action="options.php?tap=advance-settings">
  <?php wp_nonce_field('wpmls_save_setting_nonce', 'wpmls_save_setting_nonce'); ?>
  <?php settings_fields($this->options_key); ?>
  <?php do_settings_sections($this->options_key); ?>
  <?php submit_button(); ?>
</form>
