<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_searchIsUser($type, $value){

  global $wpdb;

  $type = sanitize_text_field( $type );
  if($type == '1'){
    $value = stripslashes( $value );
    $value = sanitize_email( $value );
    $searchedUser = get_user_by( 'email', $value );

    // confirm the search is valid
    if(!$searchedUser){
      // die('INVALID SERCH - mdf line 111');
        update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_007));
        return false;
    }
    // Can not be called on Admin User
    if(shgdprdm_isAdministratorRole($searchedUser)){
      update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => SHGDPRDM_war_004));
      return 'Administrator';
    }
    $ID = $searchedUser->ID;
    // $ID = $ID.' OR ID = 23';
    $userDetails = $wpdb->get_results("
    SELECT display_name AS Name, user_login AS \"User Name\", user_email AS Email, user_registered AS \"Registration Date\", ID FROM {$wpdb->prefix}users WHERE ID = {$ID}", OBJECT);
    return $userDetails;
  }
  else if($type == '2'){
    $value = sanitize_text_field( $value );
    $searchedUser = get_user_by( 'id', $value );

    // confirm the search is valid
    if(!$searchedUser){
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_008));
      return false;
    }
    // Can not be called on Admin User
    if(shgdprdm_isAdministratorRole($searchedUser)){
      update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => SHGDPRDM_war_004));
      return 'Administrator';
    }
    $ID = $value;
    // $ID = $ID.' OR ID = 23';
    $userDetails = $wpdb->get_results("
    SELECT display_name AS Name, user_login AS \"User Name\", user_email AS Email, user_registered AS \"Registration Date\", ID FROM {$wpdb->prefix}users WHERE ID = {$ID}", OBJECT);
    return $userDetails;
  }
  else{
    return false;
  }
}


// add_action('publish_post', 'send_admin_email');
function shgdprdm_sendAdminEmail($errorMsg){

    $current_user = wp_get_current_user();
    if($current_user->ID){
      $userDetails = esc_html( $current_user->display_name ).'. User ID: '.esc_html( $current_user->ID );
    }
    else{
      $userDetails = 'Unauthorised User Detected.';
    }
    $to = get_bloginfo('admin_email');
    $subject = 'GDPR Data Manager - Unauthorised Access';
    $message = $errorMsg.$userDetails;
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($to, $subject, $message, $headers );
}

function shgdprdm_validateSearchInputs($type, $value){
  $type = sanitize_text_field( $type );
  if($type == '1'){
    $value = sanitize_email( $value );
  }
  if($type == '2'){
    $value = sanitize_text_field( $value );
  }
  // Can only be called on Email or ID
  if($type != '1' && $type != '2'){
    // echo "<script>console.log('Failed on Type');</script>";
    // echo "<script>console.log('Admin Email: ".$adminEmail."');</script>";

    if(SHGDPRDM_TESTING){
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009_1));
    }
    else{
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009));
      shgdprdm_sendAdminEmail(SHGDPRDM_aem_001);
    }
    return false;
    wp_logout();
  }
  // Must have a search parameter
  if(!$value){
    // echo "<script>console.log('Failed on Value');</script>";
    update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => SHGDPRDM_war_005));
    // die('You are not authorised to access this file.');
    return false;
    wp_logout();
  }
  // Can only be a single search parameter (email)
  if($type == '1' && !is_string($value) ){
    echo "<script>console.log('Failed on Email not string');</script>";
    update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_007));
    // die('You are not authorised to access this file.');
    return false;
    wp_logout();
  }
  // Search by UserID must be numeric
  if($type == '2' && !is_numeric($value) ){
    echo "<script>console.log('Failed on ID not numeric');</script>";
    // die('You are not authorised to access this file.');
    update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_008));
    return false;
    wp_logout();
  }
  // Can only be a single search parameter
  if(strpos($value, ' ') !== false){
    // echo "<script>console.log('Failed on Multiple Inputs');</script>";
    // die('You are not authorised to access this file.');
    update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => SHGDPRDM_war_006));
    return false;
    wp_logout();
  }
  // Must be a valid email address
  if($type == '1' && !is_email($value) ){
    // echo "<script>console.log('Failed on Not Email Regex');</script>";
    // die('You are not authorised to access this file.');
    update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_007));
    return false;
    wp_logout();
  }
  if($type != '1' && $type != '2'){
    // echo "<script>console.log('Failed on Type');</script>";
    die('You are not authorised to access this file.');
    return false;
    wp_logout();
  }
  return true;
}



function shgdprdm_isAdministratorRole($searchedUser){
  $roles = '';
  foreach($searchedUser->roles as $name => $role){
    $roles.= $name.'=>'.$role.'|';
  }
  // echo "<script>console.log('User Roles: ".$roles."');</script>";
  $allowed_roles = array('editor', 'administrator', 'author');
  if( array_intersect($allowed_roles, $searchedUser->roles ) ) {
     // throw new Exception('Error! Action cannot be performed by this user (Admin User - Roles).');
     return true;
  }
  else if(user_can( $searchedUser, 'administrator' )){
    // throw new Exception('Error! Action cannot be performed by this user (Admin User - Administrator).');
    return true;
  }
  else if( user_can( $searchedUser, 'manage_options' ) ){
    // throw new Exception('Error! Action cannot be performed by this user (Admin User - Manage Options).');
    return true;
  }
  return false;
}

if( SHGDPRDM_TESTING){
  $_POST['shgdprdmscon'] = 6; // Testing incorrect type
}




// This follows from search button press
if (
  ! current_user_can('administrator') || ! current_user_can('manage_options') ||
  ! isset( $_POST['shgdprdmsch_nonce'] ) ||
  ! wp_verify_nonce( sanitize_text_field($_POST['shgdprdmsch_nonce']),
  'shgdprdm_search_action_hook' ) ||
  ! check_admin_referer( 'shgdprdm_search_action_hook',
  'shgdprdmsch_nonce' )
) {
   update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009));
   exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
}
else{

if(isset($_POST['shgdprdmscon']) && isset($_POST['shgdprdmsparam'])){

  // Validate Inputs before progressing
  // Sanitize of Post happens inside validate function
  if( shgdprdm_validateSearchInputs( $_POST['shgdprdmscon'], $_POST['shgdprdmsparam'] ) ) {
    // $result = '';
    // echo "<script>console.log('Passed All Search Validations');</script>";

    // Sanitize of Post happens inside search function
    $isUser = shgdprdm_searchIsUser($_POST['shgdprdmscon'],$_POST['shgdprdmsparam']);
    if($isUser && $isUser != 'Administrator'){
      // echo '<script>console.log("USER FOUND");</script>';
      $data['userDetails'] = $isUser;
    }
    //check if guest WooCommerce
    else if( ( !$isUser && $isUser != 'Administrator' && shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group') ) ){

      $hasOrders = false;
      if( isset(get_option('Woo-Commerce-Guest-Accounts')['Woo-Commerce-Guest-Accounts']) && get_option('Woo-Commerce-Guest-Accounts') !== null ){
        if(class_exists('SHGdprdm_WCF')){
          // exit( "Checking for Woo Commerce Guest Accounts" );
          $wcfParam = sanitize_text_field( $_POST['shgdprdmsparam'] );
          if(is_numeric( sanitize_text_field( $_POST['shgdprdmsparam'] ) ) ){
            // Cannot search guest users by ID
            // wp_die('Fail 1');
            $data['userDetails'] = false;
          }
          $orders = new SHGdprdm_WCF($wcfParam);
          $hasOrders = $orders->hasOrders();
          if(!$isUser && $hasOrders){
            $data['userDetails'] = array(0 => (object) array('Name' => 'WooCommerce Guest Customer', 'User Name' => 'Guest', 'Email' => sanitize_email($_POST['shgdprdmsparam']), 'Registration Date' => 'Guest', 'ID' => 'Guest'));
          }
          else{
            // wp_die('Fail 2');
            $data['userDetails'] = false;
          }
        } // End: If Class Exists WCom
        else{
          $data['userDetails'] = false;
        }
      } // End: If Option Set for WooComs Guest
      // if Neither Site User or WooCommerce Guest User - Check if it is a EDD User
      if( !isset($data['userDetails']) && isset(get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin']) && ( get_option('Easy-Digital-Downloads-Plugin') !== null  ) ){
        if(class_exists('SHGdprdm_EDDF')){
          // wp_die("Not Core or WC Customer");
          $eddParam = sanitize_text_field( $_POST['shgdprdmsparam'] );
          if(is_numeric( $eddParam ) ){
            // Cannot search EDD users by ID
            // wp_die('Fail 3');
            $data['userDetails'] = false;
          }
          else{
            $downloads = new SHGdprdm_EDDF($eddParam);
            // print_r($downloads);
            $isEddUser = $downloads->edd_returnCust();
            // wp_die();
            if(!$isUser && $isEddUser){
              // Swapped out EDD USer ID for 'Guest'. Otherwise failing validation condition
              $data['userDetails'] = array(0 => (object) array('Name' => $isEddUser->name, 'User Name' => 'Easy Digital Downloads Customer', 'Email' => sanitize_email($_POST['shgdprdmsparam']), 'Registration Date' => $isEddUser->date_created, 'ID' => 'Guest'));
            }
            else{
              // wp_die('Fail 4');
              $data['userDetails'] = false;
            }
          }
        } // End: If Class Exists EDD
        else{
          $data['userDetails'] = false;
        }
      } // End: If EDD Option Set
      if( !isset($data['userDetails']) ){
          // wp_die('Fail 5');
          $data['userDetails'] = false;
      }
      // else{
      //   wp_die('Fail 5');
      //   $data['userDetails'] = false;
      // }
    }
    else{
      // wp_die('Fail 6');
      $data['userDetails'] = false;
    }
  }
  // if search Inputs are not valid
  else{
    exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
    die('Search Function Failed');
  }

  // If there is valid user data
  if($data['userDetails']){
    // die('USER DATA DETECTED');
    // echo "<script>console.log('USER DATA DETECTED');</script>";

    $recordCount = count($data['userDetails']);
    if($recordCount === 1){
      $searchReturn = TRUE;
      update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => SHGDPRDM_msg_001));
    }
    elseif($recordCount > 1){
      $searchReturn = TRUE;
      update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => SHGDPRDM_msg_001));
    }
    elseif($recordCount < 1){
      $searchReturn = FALSE;
      update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => SHGDPRDM_war_001));
    }
    else{
      $searchReturn = FALSE;
      update_option('shgdprdm_admin_msg', '');
    }
    $return = base64_encode(serialize($data));
    // TESTING VALIDATIONS
    //$return = base64_encode( serialize( array( 'userDetails' => array( 0 => (object)array('Name' => 'test','User Name' => 'test', 'Email' => 'paudicompdev@gmail.com', 'Registration Date' => 'test','ID' => 'a22') ) ) ) );
    $page = esc_url( admin_url( 'admin.php').'?page=seahorse_gdpr_data_manager_plugin');
    $form = "<form id='return-data' action='".$page."' method='post' style='display:none'>";
          // $form.= "<input type='hidden' name='action' value='shgdprdm_return_action'>";
          $form.= wp_nonce_field( $page, 'shgdprdmrd_nonce' );
          $form.= "<input type='hidden' name='data' value='".$return."'></input>";
          $form.= "<input type='hidden' name='search-return' value=".$searchReturn."></input>";
          $form.= "<input type='submit' id='return-submit' name='return-submit' style='display:none;'></input>
          </form>";
          $form.= "<script>window.onload = function(){ document.getElementById('return-data').submit(); }</script>";
      echo $form;
    exit;
  }
  else{
    exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
    die('Search Function Failed');
  }
}
else{
  // echo "<script>console.log('Failed on NO INPUTS GIVEN');</script>";
  die('No Inputs Given');
}
} // end Nonce Else

?>
