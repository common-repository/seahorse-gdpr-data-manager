<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_optionPPGen(){ ?>

  <form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
    <?php settings_fields('shgdprdm_ppolicy_gen_options'); ?>
    <?php do_settings_sections('shgdprdm_ppolicy_gen_options'); ?>
    <div id="edit-shgdprdm-tandc-submit">
      <?php submit_button('Save Section'); ?>
    </div>
  </form>

<?php }

function shgdprdm_optionTandcLink() {
  
if( function_exists('shgdprdm_adminNavCheckStatus') && shgdprdm_adminNavCheckStatus() ){
?>
<form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
  <?php settings_fields('shgdprdm_tandc_options'); ?>
  <?php do_settings_sections('shgdprdm_tandc_options'); ?>
  <div id="edit-shgdprdm-tandc-submit">
    <?php submit_button('Save Section'); ?>
  </div>
</form>
<?php
} else {
?>
  <?php settings_fields('shgdprdm_tandc_options'); ?>
  <?php do_settings_sections('shgdprdm_tandc_options'); ?>
  <div id="edit-shgdprdm-tandc-submit">
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings" disabled></p>
  </div>

<?php
}

}


function shgdprdm_optionPPMng(){ ?>

  <form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
    <?php settings_fields('shgdprdm_ppolicy_mng_options'); ?>
    <?php do_settings_sections('shgdprdm_ppolicy_mng_options'); ?>
    <div id="edit-shgdprdm-tandc-submit">
      <?php submit_button('Save Section'); ?>
    </div>
  </form>

<?php }


function shgdprdm_optionPPIco(){ ?>

  <form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
    <?php settings_fields('shgdprdm_ppolicy_ico_options'); ?>
    <?php do_settings_sections('shgdprdm_ppolicy_ico_options'); ?>
    <div id="edit-shgdprdm-tandc-submit">
      <?php submit_button('Save Section'); ?>
    </div>
  </form>

<?php }


function shgdprdm_optionPPUse(){ ?>

  <form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
    <?php settings_fields('shgdprdm_ppolicy_use_options'); ?>
    <?php do_settings_sections('shgdprdm_ppolicy_use_options'); ?>
    <div id="edit-shgdprdm-tandc-submit">
      <?php submit_button('Save Section'); ?>
    </div>
  </form>

<?php }


function shgdprdm_optionPPSha(){ ?>

  <form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
    <?php settings_fields('shgdprdm_ppolicy_sha_options'); ?>
    <?php do_settings_sections('shgdprdm_ppolicy_sha_options'); ?>
    <div id="edit-shgdprdm-tandc-submit">
      <?php submit_button('Save Section'); ?>
    </div>
  </form>

<?php }


function shgdprdm_optionTandcLinkView(){?>
  <h2>Privacy Policy "Right to Data" Section </h2>
  <p>Setting for your Site's Privacy Policy in Relation to Personal Data Export, Delete and Processing</p>
  <h4>Right to access, correct and delete data and to object to data processing:</h4>
  <?php
  if( false !== get_option('shgdprdm_tandc_v2_options') ){
    $text = get_option('shgdprdm_tandc_v2_options')['tandc_v2_option'];
    if($text == ''){
      $text = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
    }
  }
  else{
    $text = SHGDPRDM_DEFAULT_TERMS_CONDITIONS_TEXT;
  }
  ?>
  <textarea id='shgdprdm_tandc_v2_option_input' name='shgdprdm_tandc_v2_options_display' rows='3' cols='6' value='<?php echo $text;?>' placeholder='<?php echo $text;?>' disabled><?php echo $text;?></textarea>
<?php
}
?>
