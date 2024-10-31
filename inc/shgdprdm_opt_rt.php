<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Replacement Text
function shgdprdm_optionReplacementText(){ ?>
<form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
  <?php settings_fields('shgdprdm_text_options'); ?>
  <?php do_settings_sections('shgdprdm_text'); ?>
  <div id="edit-shgdprdm-text-submit">
    <?php  submit_button('Save Settings'); ?>
  </div>
  <div id="edit-shgdprdm-text-option">
    <div id="edit-shgdprdm-text-option-btn" class="button button-primary">Click to Edit</div>
  </div>
</form>

<?php }


function shgdprdm_optionReplacementTextView(){ ?>
  <h2>Replacement Text Setting</h2>
  <p>Setting for Replacement Text In Database</p>
  <h4>Replacement Text:</h4>
  <?php
  if( false !== get_option('shgdprdm_text_options') ){
    $text = get_option('shgdprdm_text_options')['text_option'];
    if($text == ''){
      $text = SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB;
    }
  }
  else{
    $text = SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB;
  }
  ?>
  <textarea id='shgdprdm_text_option_input' name='shgdprdm_text_options_display' rows='3' cols='6' wrap='soft' value='<?php echo $text;?>' placeholder='<?php echo $text;?>' disabled><?php echo $text;?></textarea>
<?php
}

function shgdprdm_defaultReplacementText(){ ?>
  <h2>Replacement Text Setting</h2>
  <p>Setting for Replacement Text In Database</p>
  <h4>Default Replacement Text that will be applied:</h4>
  <?php
  if( false !== get_option('shgdprdm_text_options') ){
    $text = get_option('shgdprdm_text_options')['text_option'];
    if($text == ''){
      $text = SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB;
    }
  }
  else{
    $text = SHGDPRDM_DEFAULT_REPLACEMENT_TEXT_IN_DB;
  }?>
  <div class="shgdprdm-faux-input" style="background-color:#fff;border:1px solid grey;border-radius:3px;line-height:1.3;padding-right:20px;padding-left:20px;display:inline-block;"><h3><em>"<?php echo $text;?>"</em></h3></div>
  <div class="shgdprdm-upgrade-to-pro-notice">
    <h2> Upgrade to the PRO Version to Customise the Replacement Text in Database</h2>
  </div>
  <a class="button button-primary" href="<?php echo SHGDPRDM_VALIDATE_DEFAULT_URL;?>/downloads/gdpr-data-manager/" target="_blank">
    <div id="shgdprdm-upgrade-to-pro-container">
      <div class="shgdprdm-upgrade-to-pro-notice">
        Upgrade to PRO Now
      </div>
    </div>
  </a>
<?php  
}

?>
