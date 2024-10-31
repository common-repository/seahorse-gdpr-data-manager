<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Privacy Policy Link
function shgdprdm_optionPpolicyLink(){ 

if( function_exists('shgdprdm_adminNavCheckStatus') && shgdprdm_adminNavCheckStatus() ){
?>
<form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
  <?php settings_fields('shgdprdm_ppolicy_options'); ?>
  <?php do_settings_sections('shgdprdm_ppolicy'); ?>

  <div id="edit-shgdprdm-ppolicy-submit">
    <?php submit_button('Save Settings'); ?>
  </div>
</form>
<?php
} else {
?>

  <?php settings_fields('shgdprdm_ppolicy_options'); ?>
  <?php do_settings_sections('shgdprdm_ppolicy'); ?>

  <div id="edit-shgdprdm-ppolicy-submit">
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings" disabled></p>
  </div>

<?php
}

}

function shgdprdm_optionPpolicyLinkView(){?>
  <!--<h2>Privacy Policy Link Setting</h2>-->
  <p>Setting for your Site's Privacy Policy in Relation to Personal Data Export & Delete</p>
  <h4>Privacy Policy Link:</h4>
  <?php
  if( false !== get_option('shgdprdm_ppolicy_options') ){
    $text = get_option('shgdprdm_ppolicy_options')['ppolicy_option'];
    if($text == ''){
      $text = SHGDPRDM_DEFAULT_PRIVACY_POLICY_LINK;
    }
  }
  else{
    $text = SHGDPRDM_DEFAULT_PRIVACY_POLICY_LINK;
  }
  ?>
  <label class='shgdprdm-option-label' for='shgdprdm_ppolicy_option_input'><?php echo get_bloginfo('url');?>/</label>
  <input id='shgdprdm_ppolicy_option_input' name='shgdprdm_ppolicy_display' type='text' value='<?php echo $text;?>' disabled/>
  <!-- <hr>
  <a href="#"></a> -->

<?php
}
?>
