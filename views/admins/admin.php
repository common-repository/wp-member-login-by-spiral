<?php

/**
 * Represents the view for admin setting form
 *
 * @package   Spiral_Member_Login
 * @author    PIPED BITS Co.,Ltd.
 */
if (!defined('ABSPATH')) {
  exit;
}
include_once(plugin_dir_path(__DIR__) . 'forms/toggle_version.php');
$is_setup = $this->get_query_param('setup');
$get_tab = $this->get_query_param('tab');
$get_status = $this->get_query_param('status');
$tab = isset($get_tab) ? $get_tab : null;
$action = str_replace("http://", "https://", WPMLS_CUSTOM_OPTIONS);
?>

<?php if (((get_option('version') == 1) && (!get_option('is_setup')))) : ?>

  <div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <?php
    printf(
      esc_html__('Your setting is ', 'spiral-member-login') . '<b>SPIRAL ver.1</b>'
    );
    (get_locale() == "ja") ? printf(" です。") : '';
    ?>
    <?php if (!is_null($get_status)) : ?>
      <div id="message" class="notice notice-success is-dismissible">
        <p><strong><?php esc_html_e('Cache Cleared.', 'spiral-member-login'); ?></strong></p>
      </div>
    <?php endif; ?>
    <nav class="nav-tab-wrapper">
      <a tab="1" href="?page=spiral_member_login" class="nav-tab <?php if ($tab === null) : ?>nav-tab-active<?php endif; ?>">
        <?php esc_html_e('Basic Settings', 'spiral-member-login') ?>
      </a>
      <a tab="2" href="?page=spiral_member_login&tab=advance-settings" class="nav-tab <?php if ($tab === 'advance-settings') : ?>nav-tab-active<?php endif; ?>">
        <?php esc_html_e('Advanced Settings', 'spiral-member-login'); ?>
      </a>
    </nav>

    <div class="tab-content">
      <?php
      switch ($tab):
        case 'advance-settings':
          include(plugin_dir_path(__DIR__) . 'forms/advance_settings_form.php');
          break;
        default:
          include(plugin_dir_path(__DIR__) . 'forms/basic_settings_form.php');
      endswitch;
      ?>
    </div>
  </div>
  <?php
  ?>
<?php endif; ?>
<?php if (((get_option('version') == 2) &&  (!get_option('is_setup')))) { ?>
  <div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    <?php
    printf(
      esc_html__('Your setting is ', 'spiral-member-login') . '<b>SPIRAL ver.2</b>'
    );
    (get_locale() == "ja") ? printf(" です。") : '';
    ?>
    <?php
    if (!is_null($get_status)) {
    ?><div id="message" class="notice notice-success is-dismissible">
        <p><strong><?php esc_html_e('Cache Cleared.', 'spiral-member-login'); ?></strong></p>
      </div>
    <?php
    }
    ?>
    <nav class="nav-tab-wrapper">
      <a tab="1" class="nav-tab nav-tab-active">
        <?php
        esc_html_e('Basic Settings', 'spiral-member-login');
        ?>
      </a>
    </nav>
    <div class="tab-content">
      <?php
      echo '<form id="setting-form" name="frmSetting" method="POST" action="'.esc_attr($action).'">';
      echo '<input type="hidden" name="form_name" value="wpmls_save_setting_form">';
      wp_nonce_field('wpmls_save_setting_nonce', 'wpmls_save_setting_nonce');
      settings_fields($this->options_key);
      do_settings_sections($this->options_key);
      submit_button();
      echo '</form>';
      ?>
    </div>
  </div>
  <?php
  ?>
<?php } ?>
<?php if (!get_option('is_setup')) : ?>
  <div class="mb-5">
    <?php
    include_once(plugin_dir_path(__DIR__) . 'forms/clear_cache.php');
    ?>
  </div>
  <div>
    <?php
    include_once(plugin_dir_path(__DIR__) . 'forms/clear_setting.php');
    ?>
  </div>
  <?php
  include(plugin_dir_path(__DIR__) . 'scripts/confirm_modal.php');
  include(plugin_dir_path(__DIR__) . 'scripts/confirm_script.php');
  ?>
<?php endif; ?>