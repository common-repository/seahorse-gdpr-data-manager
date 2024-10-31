<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Search By
function shgdprdm_optionSearchBy() { ?>
<form method="post" action="<?php echo esc_url(admin_url());?>options.php">
  <?php settings_fields('shgdprdm_search_options');?>
  <?php do_settings_sections('shgdprdm_plugin'); ?>
  <div style="display:inline-block">
    <?php  submit_button('Save Settings'); ?>
  </div>
</form>

<?php }

function shgdprdm_optionSearchByView() { ?>
  <h2>User Search Option</h2>
  <p>Option for how User Data is Searched</p>
  <h4>Search By:</h4>
  <div class='shgdprdm-checkboxgroup'>
  <input type='radio' id='shgdprdm_search_options[search_option][email]' name='shgdprdm_search_options_display' class='shgdprdm-radio-email' value='1' checked />
  <label for='shgdprdm_search_options[search_option][email]'>User Email</label>
  </div>
  <div class='shgdprdm-checkboxgroup'>
  <input type='radio' id='shgdprdm_search_options[search_option][id]' name='shgdprdm_search_options_display' class='shgdprdm-radio-id' value='2' />
  <label for='shgdprdm_search_options[search_option][id]'>User ID</label>
  </div>
<?php }

function shgdprdm_defaultSearchBy() { ?>
  <div class="shgdprdm-upgrade-to-pro-notice">
    <h2><?php echo SHGDPRDM_PRO_UPGRADE_TEXT; ?></h2>
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
