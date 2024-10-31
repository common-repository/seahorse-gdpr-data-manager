<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_validateExtRecordRef($refNum){
  if(!$refNum){
    return FALSE;
  }
  if( !is_numeric($refNum) ){
    return FALSE;
  }
  if( $refNum === 0 || $refNum === '0' || $refNum == 0 || $refNum == '0' ){
    return FALSE;
  }
  return TRUE;
}


// This follows from sync button press
if ( ! current_user_can( 'administrator' ) ) {
   update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' - <em>ref(exsy 01)</em>'));
   exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
}
else if ( ! current_user_can( 'manage_options' ) ) {
   update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' - <em>ref(exsy 02)</em>'));
   exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
}
else if ( ! isset( $_POST['shgdprdmexsy_nonce'] ) ) {
   update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' - <em>ref(exsy 03)</em>'));
   exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
}
else if ( ! wp_verify_nonce( sanitize_text_field($_POST['shgdprdmexsy_nonce']), 'shgdprdm_external_sync' ) ) {
   update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' - <em>ref(exsy 04)</em>'));
   exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
}
else if ( ! check_admin_referer( 'shgdprdm_external_sync','shgdprdmexsy_nonce' ) ) {
   update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' - <em>ref(exsy 05)</em>'));
   exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
}
else{
  if( isset($_POST['shgdprdm_sync_ext_record_ref']) ){
    $records = "Record Ref: ";
    foreach($_POST['shgdprdm_sync_ext_record_ref'] as $recordRef){
      $recordRef = sanitize_text_field($recordRef);
      if(isset($_POST['shgdprdm_sync_ext_record_submit_'.$recordRef])){
        $validateRecordRef = $recordRef;
      }
    }
    // wp_die($records.$validateRecordRef);

    // Validate Inputs before progressing
    if( isset($validateRecordRef) && shgdprdm_validateExtRecordRef( $validateRecordRef ) ) {
      if( class_exists('SHGdprdm_SYNCRECORD') ){
        // wp_die('Found Class');
        try{
          $sync = new SHGdprdm_SYNCRECORD( $validateRecordRef );
          $recordRef = $sync->shgdprdm_getUpdatedRef();
          if($recordRef){
            update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => 'Local Database Synced with Record Ref. '.$recordRef));
            if( $sync->shgdprdm_getAdminAction() ){
              $recordEmail = ( $sync->shgdprdm_getRecordEmail() ? $sync->shgdprdm_getRecordEmail() : 'Unknown');
              // $recordEmail = ( $recordEmail ?  $recordEmail : 'UNKNOWN?' );
              update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => '<strong>ACTION REQUIRED!</strong><br>Local Database Synced with Record Ref. '.$recordRef.'<br>Please re-process the delete action using the Search Option'));

              if(false === get_option('shgdprdm_sync_delete')) {
          			add_option('shgdprdm_sync_delete', array('redelete' => 1, 'dRef' => $recordEmail) );
          		}
              else {
          			update_option('shgdprdm_sync_delete', array('redelete' => 1, 'dRef' => $recordEmail) );
          		}
              exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
            }
          }
          else{
            update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => 'Local Database cannot be synced at this time'));
          }
          exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_review_history' ) ) );
        }
        catch(Exception $e){
          wp_die($e->getMessage());
          // return;
        }
      }
      else{

        wp_die('Cant find Class');
        update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009));
        exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
      }
      // $result = '';
      // echo "<script>console.log('Passed All Search Validations');</script>";
      // $isUser = shgdprdm_searchIsUser($_POST['shgdprdmscon'],$_POST['shgdprdmsparam']);
      // if($isUser && $isUser != 'Administrator'){
      //   // echo '<script>console.log("USER FOUND");</script>';
      //   $data['userDetails'] = $isUser;
      // }
      // //check if guest WooCommerce
      // else if( !$isUser && $isUser != 'Administrator' && shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group') ){
      //   if( get_option('Woo-Commerce-Guest-Accounts') !== null ){
      //     $wcfParam = $_POST['shgdprdmsparam'] ;
      //     if(is_numeric($_POST['shgdprdmsparam'])){
      //       // Cannot search guest users by ID
      //       $data['userDetails'] = false;
      //     }
      //     $orders = new SHGdprdm_WCF($wcfParam);
      //     $hasOrders = $orders->hasOrders();
      //     if(!$isUser && $hasOrders){
      //       $data['userDetails'] = array(0 => (object) array('Name' => 'WooCommerce Guest Customer', 'User Name' => 'Guest', 'Email' => $_POST['shgdprdmsparam'], 'Registration Date' => 'Guest', 'ID' => 'Guest'));
      //     }
      //     else{
      //       $data['userDetails'] = false;
      //     }
      //   }
      //   else{
      //     $data['userDetails'] = false;
      //   }
      // }
      // else{
      //   $data['userDetails'] = false;
      // }
    }
    // if search Inputs are not valid
    else{
      // wp_die('Sync Failed');
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => 'Record Synchronisation Failed'));
      exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
      die('Sync Function Failed');
    }

    // // If there is valid user data
    // if($data['userDetails']){
    //   // die('USER DATA DETECTED');
    //   // echo "<script>console.log('USER DATA DETECTED');</script>";
    //
    //   $recordCount = count($data['userDetails']);
    //   if($recordCount === 1){
    //     $searchReturn = TRUE;
    //     update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => SHGDPRDM_msg_001));
    //   }
    //   elseif($recordCount > 1){
    //     $searchReturn = TRUE;
    //     update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => SHGDPRDM_msg_001));
    //   }
    //   elseif($recordCount < 1){
    //     $searchReturn = FALSE;
    //     update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => SHGDPRDM_war_001));
    //   }
    //   else{
    //     $searchReturn = FALSE;
    //     update_option('shgdprdm_admin_msg', '');
    //   }
    //   $return = base64_encode(serialize($data));
    //   // TESTING VALIDATIONS
    //   //$return = base64_encode( serialize( array( 'userDetails' => array( 0 => (object)array('Name' => 'test','User Name' => 'test', 'Email' => 'paudicompdev@gmail.com', 'Registration Date' => 'test','ID' => 'a22') ) ) ) );
    //   $page = esc_url( admin_url( 'admin.php').'?page=seahorse_gdpr_data_manager_plugin');
    //   $form = "<form id='return-data' action='".$page."' method='post' style='display:none'>";
    //         // $form.= "<input type='hidden' name='action' value='shgdprdm_return_action'>";
    //         $form.= wp_nonce_field( $page, 'shgdprdmrd_nonce' );
    //         $form.= "<input type='hidden' name='data' value='".$return."'></input>";
    //         $form.= "<input type='hidden' name='search-return' value=".$searchReturn."></input>";
    //         $form.= "<input type='submit' id='return-submit' name='return-submit' style='display:none;'></input>
    //         </form>";
    //         $form.= "<script>window.onload = function(){ document.getElementById('return-data').submit(); }</script>";
    //     echo $form;
    //   exit;
    // }
    // else{
    //   exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
    //   die('Search Function Failed');
    // }
  }
  else{
    // echo "<script>console.log('Failed on NO INPUTS GIVEN');</script>";
    wp_die('No Inputs Given');
  }
} // end Nonce Else
?>
