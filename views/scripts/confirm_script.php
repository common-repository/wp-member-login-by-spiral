<?php
if (!defined('ABSPATH')) exit;
?>
<script>
    document.querySelector(".clear_cache").addEventListener('click', function(e) {
    location.href = location.href + "&clear_cache=true";
  })
  var isChanged = false;
  var isClearSpiralSetting = false;
  const form = document.getElementById('setting-form');
  var clear_spiral_setting_form = document.querySelector('#clear-spiral-setting-form');

  form.addEventListener('change', function(e) {
    isChanged = true;
  });

  clear_spiral_setting_form.addEventListener('submit', function(e) {
    isClearSpiralSetting = true;
    e.preventDefault();
    switchTab1(e);
  });

  const ui = {
    confirm: async (message) => {
      return new Promise((resolve, reject) => {
        const createConfirm = () => {
          const confirmDiv = document.querySelector('.confirm');
          const confirmMessage = document.getElementById('confirmMessage');
          const confirmYes = document.getElementById('confirmYes');
          const confirmNo = document.getElementById('confirmNo');
          const confirmClose = document.getElementById('confirmClose');

          confirmMessage.textContent = message;

          const yesHandler = () => {
            confirmDiv.style.display = 'none';
            resolve(true);
          };

          const noHandler = () => {
            confirmDiv.style.display = 'none';
            resolve(false);
          };

          const closeHandler = () => {
            confirmDiv.style.display = 'none';
            resolve(false);
            reject('Closed');
          };

          confirmYes.removeEventListener('click', yesHandler);
          confirmNo.removeEventListener('click', noHandler);
          confirmClose.removeEventListener('click', closeHandler);

          confirmYes.addEventListener('click', yesHandler);
          confirmNo.addEventListener('click', noHandler);
          confirmClose.addEventListener('click', closeHandler);

          confirmDiv.style.display = 'block';
        };

        createConfirm();
      });
    }
  };

  /**
   * Clear Cache
   */
  function check() {
    isChanged = true;
    switchTab1();
  }

  /**
   * END Clear Cache
   */
  var tabs = document.querySelectorAll("nav > a");
  var basicConfig = document.querySelectorAll(".basic_config");

  for (var i = 0; i < basicConfig.length; i++) {
    basicConfig[i].addEventListener("change", textChange);
  }

  function textChange(event) {
    isChanged = true;
    this.setAttribute("value", event.target.value);
  }

  for (var i = 0; i < tabs.length; i++) {
    tabs[i].addEventListener("click", switchTab1);
  }

  async function switchTab1(event) {
    if (isChanged) {
      let tab = event.target.getAttribute("tab");
      let href = event.target.getAttribute("href");
      event.preventDefault();

      if (tab == 2) {
        document.getElementsByName('_wp_http_referer')[0].value += '&tab=advance-settings';
      } else {
        document.getElementsByName('_wp_http_referer')[0].value = '/wordpress/wp-admin/options-general.php?page=spiral_member_login';
      }
      const confirm = await ui.confirm('変更を保存しますか?');
      if (confirm) {
        if (tab == 1) {
          document.querySelector("#setting-form").submit.click()
        } else {
          document.querySelector("#setting-form").submit.click()
        }
      } else {
        window.location.href = href;
      }
    }
    // Clear Siral Setting
    if(isClearSpiralSetting){
      var text = "<?php _e("All SPIRAL settings' data will be reset. Are you sure?",'spiral-member-login') ?>";
      const confirm = await ui.confirm(text);
      if (confirm) {
        document.clearSettingForm.submit();
      }else{
        isClearSpiralSetting = false;
      }
    }
  }

  $setting_message = document.querySelector(".clear_cache_button");

  if ($setting_message != null) {
    document.querySelector(".clear_cache_button").addEventListener("click", function(e) {
      e.preventDefault();
      document.getElementById("setting-error-settings_updated").classList.add('none');
    })
  }
</script>