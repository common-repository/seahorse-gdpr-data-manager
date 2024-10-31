<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Admin Settings Page for Privacy Policy & Associated Items
// Settings & Options Moved to Main page
function shgdprdm_privacyPolicyOptionsPage()
// function shgdprdm_searchOptionsPage()
{
    if (shgdprdm_getUserNotice()) {
        echo shgdprdm_getUserNotice();
    }

    $lang = get_bloginfo("language");
    $lang = get_locale();

    $shgdprdm_sc_val = '[gdm-privacy-policy-custom lang=\'' . $lang  . '\']';

    

    if (class_exists('SHGdprdm_ValidateControl')) {
        $validateControl = new SHGdprdm_ValidateControl;
        if ($validateControl->shgdprdm_validateVerifyLicence() &&
            $validateControl->shgdprdm_validateHasLicence() 
            // &&
            // ($validateControl->shgdprdm_validateIsProLicence('wcf') || $validateControl->shgdprdm_validateIsProLicence('eddf'))
        ) {
            $shgdprdm_sc_val = '[gdm-privacy-policy-custom-pro lang=\'' . $lang  . '\']';
        }
    }
    ?>

    <div id="Shgdprdm-options-page-options-fullpage">
    <div id="Shgdprdm-options-page-options">
      
        <h3>Privacy Policy Statement</h3>
        <?php
        ///echo $lang;
        ///echo "<br>";
        //print_r(get_option('shgdprdm_ppolicy_gen_options'));
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">Short Code [short-code]:</th>
                <td>
                    <input type="text" id="gdm-privacy-policy-custom-shortcode" name="gdm-privacy-policy-custom-shortcode" class="widefat" readonly="readonly" value="<?php echo $shgdprdm_sc_val; ?>">
                    <p style="font-size: 11px;">
                        Copy and paste this shortcode into your Privacy Policy Page.
                    </p>
                </td>
            </tr>
        </table>
      
        <div class="pp-disclaimer-highlight">
        <p>
        <?php echo SHGDPRDM_DEFAULT_PRIVACY_POLICY_DISCLAIMER_TEXT; ?>
        </p>
        </div>
      
        <p><hr></p>
      
        <div class="pp-section-highlight">

        <div id="shgdprdm-tandc-gen-option-container">
            <?php echo shgdprdm_optionPPGen(); ?>
        </div>
        
        </div>
      
      <p><hr></p>
      
      <div class="rtd-section-highlight">

      <div id="shgdprdm-tandc-option-container">
        <?php echo shgdprdm_optionTandcLink(); ?>
      </div>
      
      
       <div id="shgdprdm-ppolicy-option-container">
        <?php echo shgdprdm_optionPpolicyLink(); ?>
      </div>
      
      </div>
      
      <p><hr></p>
      
      
      <div class="pp-section-highlight">

      <div id="shgdprdm-tandc-gen-option-container">
        <?php echo shgdprdm_optionPPMng(); ?>
      </div>
      
      </div>
      
      <p><hr></p>
      
      <div class="pp-section-highlight">

      <div id="shgdprdm-tandc-gen-option-container">
        <?php echo shgdprdm_optionPPIco(); ?>
      </div>
      
      </div>
      
      <p><hr></p>
      
      <div class="pp-section-highlight">

      <div id="shgdprdm-tandc-gen-option-container">
        <?php echo shgdprdm_optionPPUse(); ?>
      </div>
      
      </div>
      
      <p><hr></p>
      
      <div class="pp-section-highlight">

      <div id="shgdprdm-tandc-gen-option-container">
        <?php echo shgdprdm_optionPPSha(); ?>
      </div>
      
      </div>
      
      <p><hr></p>

    </div>

    <div id="Shgdprdm-options-page-preview">
      <?php echo shgdprdm_optionUserPagePreviewView(); ?>
    </div>
  </div>
  <?php
  // }
  // else{
  //   echo shgdprdm_makeNoLicenceActionSectionOptionsView();
  //   echo shgdprdm_makeNoLicenceNoticeOpenTags();
  //   echo shgdprdm_makeNoLicenceNotice();
  //   echo shgdprdm_makeNoLicenceActionSection('sh_general');
  //   echo shgdprdm_makeNoLicenceNoticeCloseTags();
  // }
}

function shgdprdm_getSearchOption()
{
    $seahorse_shgdprdm_search_options = get_option('shgdprdm_search_options');
    if (!$seahorse_shgdprdm_search_options['search_option'] || $seahorse_shgdprdm_search_options['search_option'] == '1') {
        $searchByName = 'Email Address';
    } elseif ($seahorse_shgdprdm_search_options['search_option'] == '2') {
        $searchByName = 'ID Number';
    }
    return $searchBy = array($searchByName, $seahorse_shgdprdm_search_options['search_option']);
}
?>
