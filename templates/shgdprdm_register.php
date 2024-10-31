<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_registerLicencePage()
{
    if (shgdprdm_getUserNotice()) {
        echo shgdprdm_getUserNotice();
    } ?>
  <div class="container">
    <div>
      <h2>Register Licence</h2>
    </div>
    <div><hr></div>

    <!-- Registration / Licence Key -->
    <div id="shgdprdm-reglicnum-container">
      <?php echo shgdprdm_registerLicenceNumberSection(); ?>
    </div>
      <div><hr></div>

  </div>
<?php
}

function shgdprdm_registerLicencePageNavTab() { ?>
  <div>
    <h2 class="nav-tab-wrapper">
      <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_register_licence" class="nav-tab nav-tab-active">
        <span class="shgdprdm_icon-xl dashicons dashicons-admin-network"></span>
        &nbsp;&nbsp;Register Licence</a>
    </h2>
  </div>

<?php }

?>
