<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$is_settup = $this->get_query_param('setup');
if(!is_null($is_settup)){
  update_option('is_setup',true);
}
if ( get_option('is_setup') ) { ?>
  <form  method="post" id="choouse_version" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <table class="form-table" role="presentation">
      <tr>
        <th scope="row">
          <h2><?php esc_html_e('WP Member Login by SPIRAL','spiral-member-login'); ?></h2>
          <p>
            <?php esc_html_e('Please choose your SPIRAL version','spiral-member-login'); ?>
          </p>
        </th>
      </tr>
      <tr>
        <td id="front-static-pages">
          <fieldset>
            <legend class="screen-reader-text"><span><?php esc_html_e('Your homepage displays','spiral-member-login'); ?></span></legend>
            <p><label>
                <input name="show_on_front" type="radio" value="1" class="tog" required />
                <?php esc_html_e('SPIRAL ver.1','spiral-member-login'); ?>
              </label>
            </p>
            <p><label>
                <input name="show_on_front" type="radio" value="2" class="tog" required />
                <?php esc_html_e('SPIRAL ver.2','spiral-member-login'); ?>
              </label>
            </p>
            <input type="hidden" name="form_name" value="<?php echo esc_attr( 'switch_version_form' ); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr( 'switch_version_form_action' ); ?>" />
            <?php wp_nonce_field( 'spiral_version', 'spiral_version' ); ?>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Next','spiral-member-login'); ?>">
            </p>
          </fieldset>
        </td>
      </tr>
    </table>
  </form>
<?php } ?>