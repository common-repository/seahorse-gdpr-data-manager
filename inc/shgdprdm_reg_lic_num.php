<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

// Register Licence Number
function shgdprdm_registerLicenceNumberSection(){ ?>
  <form method="post" action="<?php echo esc_url( admin_url() );?>options.php">
    <?php settings_fields('shgdprdm_reglicnum_options'); ?>
    <?php do_settings_sections('shgdprdm_reglicnum'); ?>
    <div id="edit-shgdprdm-reglicnum-submit">
      <?php
      if( shgdprdm_checkIfLocalValid() ){
        $attr = array( 'id' => 'shgdprdm_deactivate_licence', 'title' => 'Deactivate This Licence');
        submit_button('Deactivate This Licence', 'primary shgdprdm_verify_delete', 'shgdprdm_reglicnum_options[shgdprdm_deactivate_licence]', true, $attr);
      }
      else{
        $attr = array( 'id' => 'shgdprdm_register_licence', 'title' => 'Register This Licence');
        submit_button('Register This Licence', 'primary', 'shgdprdm_reglicnum_options[shgdprdm_register_licence]', true, $attr);
      }
      ?>
    </div>
  </form>
  <?php
  if( shgdprdm_isLocalPopulated() ){
    echo shgdprdm_checkLicenceStatus(true);
  }
} // Function end


function shgdprdm_checkLicenceStatus($print=NULL){
  if(class_exists('SHGdprdm_ValidateLicenceKey')){
    try{
      $licenceValidate = new SHGdprdm_ValidateLicenceKey( 'check-licence' );
      $response = $licenceValidate->shgdprdm_validate();
      $expiry = '';
      $status = '';

      if($response){
        $expiry = $licenceValidate->shgdprdm_getExpiry();
        $status = $licenceValidate->shgdprdm_getStatus();
      }
      if($print){
        echo shgdprdm_printLicenceDetails($response, $expiry, $status);
      }

      // Destruct
      $licenceValidate = null;
      return FALSE;
    }
    catch(Exception $e){
      $title = 'Error Validating Licence Key. Please Contact <a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'" target="_blank">Seahorse Data Management</a> '.$e->getMessage();
      $class = 'shgdprdm-licence-check shgdprdm-not-valid dashicons-warning';
      wp_die( "<div class='shgdprdm-licence-check-container'><span class='shgdprdm_icon-xl dashicons {$class}'></span><p>{$title}</p></div>");
    }
  }
}

function shgdprdm_checkLicenceValid($print=NULL){
  if(class_exists('SHGdprdm_ValidateLicenceKey')){
    if(false === get_option('shgdprdm_adminHasLicence')){
      return '';
    }
    try{
      $licenceValidate = new SHGdprdm_ValidateLicenceKey( 'activate' );
      // Destruct
      $licenceValidate = null;
      return FALSE;
    }
    catch(Exception $e){
      $title = 'Error Validating Licence Key. Please Contact <a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'" target="_blank">Seahorse Data Management</a> '.$e->getMessage();
      $class = 'shgdprdm-licence-check shgdprdm-not-valid dashicons-warning';
      wp_die( "<div class='shgdprdm-licence-check-container'><span class='shgdprdm_icon-xl dashicons {$class}'></span><p>{$title}</p></div>");
    }
  }
}

function shgdprdm_deactivateValidLicence($print=NULL){
  if(class_exists('SHGdprdm_ValidateLicenceKey')){
    try{
      $licenceValidate = new SHGdprdm_ValidateLicenceKey( 'deactivate' );
      // Destruct
      $licenceValidate = null;
      return FALSE;
    }
    catch(Exception $e){
      $title = 'Error Deactivating Licence Key. Please Contact <a href="'.SHGDPRDM_VALIDATE_DEFAULT_URL.'" target="_blank">Seahorse Data Management</a> '.$e->getMessage();
      $class = 'shgdprdm-licence-check shgdprdm-not-valid dashicons-warning';
      wp_die( "<div class='shgdprdm-licence-check-container'><span class='shgdprdm_icon-xl dashicons {$class}'></span><p>{$title}</p></div>");
    }
  }
}


function shgdprdm_printLicenceDetails($response, $expiry, $status){
  if( $response && $expiry && $status ){
    if($status === 'valid'){
      $title = 'Valid Licence Number';
      $class = 'shgdprdm-licence-check shgdprdm-valid dashicons-yes';
    }
    else{
      $title = 'Inactive Licence Number';
      $class = 'shgdprdm-licence-check shgdprdm-inactive dashicons-warning';
    }
    if($expiry){
      $expires = "<em>( Expires: {$expiry} )</em>";
    }
    else{
      $expires = "<em>( Expires: Unavailable )</em>";
    }
  }
  else{
    $title = 'Warning! Licence Number Not Valid';
    $class = 'shgdprdm-licence-check shgdprdm-not-valid dashicons-no';
    $expires = '';
    if(
      false !== get_option('shgdprdm_adminVerifyLicence') &&
      get_option('shgdprdm_adminVerifyLicence')['licence_msg'] === 'no_activations_left'
    ){
      $title .= ' - Activation limit exceeded';
    }
  }
  return "<div class='shgdprdm-licence-check-container'><span class='shgdprdm_icon-xl dashicons {$class}'></span><p>{$title} {$expires}</p></div>";
}

function shgdprdm_validateLicenceKey(){
  if(
    false === get_option('shgdprdm_adminVerifyLicence') ||
    ( false !== get_option('shgdprdm_adminVerifyLicence') &&
      get_option('shgdprdm_adminVerifyLicence')['licence_valid'] !== true
    )
  ){
    shgdprdm_checkLicenceValid();
  }
}

function shgdprdm_deactivateLicenceKey(){
  if(
    false !== get_option('shgdprdm_adminVerifyLicence') &&
    get_option('shgdprdm_adminVerifyLicence')['licence_valid'] === true
  ){
    shgdprdm_deactivateValidLicence();
  }
}

function shgdprdm_checkIfLocalValid(){
  if( false !== get_option('shgdprdm_adminVerifyLicence') &&
      get_option('shgdprdm_adminVerifyLicence')['licence_valid'] === true
    ){
      return TRUE;
    }
    return FALSE;
}

function shgdprdm_isLocalPopulated(){
  if( false === get_option('shgdprdm_adminVerifyLicence') ||
      ( get_option('shgdprdm_adminVerifyLicence')['licence_number'] === false &&
        get_option('shgdprdm_adminVerifyLicence')['licence_valid'] === false &&
        get_option('shgdprdm_adminVerifyLicence')['licence_msg'] === false
      )
      ||
        ( !get_option('shgdprdm_adminVerifyLicence')['licence_number'] &&
        !get_option('shgdprdm_adminVerifyLicence')['licence_valid'] &&
        !get_option('shgdprdm_adminVerifyLicence')['licence_msg']
      )
    ){
      return FALSE;
    }
    return TRUE;
}
?>
