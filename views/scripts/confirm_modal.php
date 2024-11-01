<?php
if (!defined('ABSPATH')) exit;
?>
<div class="confirm">
  <div></div>
  <div>
    <div id="confirmMessage"></div>
    <button id="confirmClose" type="button" class="notice-dismiss"></button>

    <div class="flex justify-center">
      <input id="confirmYes" type="button" value="<?php esc_attr_e('Yes','spiral-member-login') ?>" />
      <input id="confirmNo" type="button" value="<?php esc_attr_e('No','spiral-member-login')?>" />
    </div>
  </div>
</div>