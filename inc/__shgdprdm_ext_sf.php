<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_search($type, $value){
  ////echo "<script>console.log('In search function: ".$value."');</script>";


  global $wpdb;


      if($type == '1'){
        $value = stripslashes( $value );
        $searchedUser = get_user_by( 'email', $value );

        // confirm the search is valid
        if(!$searchedUser){
            update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => err_007));
            return false;
        }

        $ID = $searchedUser->ID;
        $testExtra = "";
      }
      else if($type == '2'){
        $searchedUser = get_user_by( 'id', $value );

        // confirm the search is valid
        if(!$searchedUser){
          update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => err_008));
          return false;
        }


        $ID = $value;
        $testExtra = "";
      }
      $userDetails = $wpdb->get_results("
      SELECT display_name AS Name, user_login AS \"User Name\", user_email AS Email, user_registered AS \"Registration Date\", ID FROM {$wpdb->prefix}users WHERE ID = {$ID} {$testExtra}", OBJECT);

      if(count($userDetails) == 1){
        // Run full search
        $table = 'users';

        $tableList = array('usr' => 'users');
        $select = '';
        $from = '';
        foreach($tableList as $prefix => $name){
          $select .= $prefix.'.*, ';
          $from .= $wpdb->prefix.$name.' '.$prefix.', ';
        }
        $select = substr($select, 0, -2);
        $from = substr($from, 0, -2);
        $searchID = $userDetails[0]->ID;

        $tableList = shgdprdm_getAllIdColumnNames();
        $select = '*';
        $tableCount = 0;
        $dataTableCount = 0;
        foreach($tableList as $cat => $tblData){

          foreach($tblData as $tableName => $condition){
            $tableCount++;
            if($condition){
              $dataTableCount++;
              $tableDetails[$tableName] = $wpdb->get_results("SELECT {$select} FROM {$wpdb->prefix}{$tableName} WHERE {$condition} = {$searchID}", OBJECT);
            }
          }

        }
        $tableDetails['shgdprdm_tableCount'] = $tableCount;
        $tableDetails['shgdprdm_dataTableCount'] = $dataTableCount;




        update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => msg_001));
        return $data = array('userDetails' => $userDetails, 'tableDetails' => $tableDetails);
      }
      else if(count($userDetails) > 1){
        update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => war_002));
        return $data = array('userDetails' => $userDetails);
      }

      return false;
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
  // Can only be called on Email or ID
  if($type != '1' && $type != '2'){
  ////  echo "<script>console.log('Failed on Type');</script>";
  ////  echo "<script>console.log('Admin Email: ".$adminEmail."');</script>";

    update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => err_009));
    shgdprdm_sendAdminEmail(aem_001);
    return false;
    wp_logout();
  }
  // Must have a search parameter
  if(!$value){
    ////echo "<script>console.log('Failed on Value');</script>";
    update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => war_005));
    // die('You are not authorised to access this file.');
    return false;
    wp_logout();
  }
  // Can only be a single search parameter (email)
  if($type == '1' && !is_string($value) ){
    ///echo "<script>console.log('Failed on Email not string');</script>";
    update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => err_007));
    // die('You are not authorised to access this file.');
    return false;
    wp_logout();
  }
  // Search by UserID must be numeric
  if($type == '2' && !is_numeric($value) ){
  ////  echo "<script>console.log('Failed on ID not numeric');</script>";
    // die('You are not authorised to access this file.');
    update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => err_008));
    return false;
    wp_logout();
  }
  // Can only be a single search parameter
  if(strpos($value, ' ') !== false){
  ////  echo "<script>console.log('Failed on Multiple Inputs');</script>";
    // die('You are not authorised to access this file.');
    update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => war_006));
    return false;
    wp_logout();
  }
  // Must be a valid email address
  if($type == '1' && !is_email($value) ){
  ////  echo "<script>console.log('Failed on Not Email Regex');</script>";
    // die('You are not authorised to access this file.');
    update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => err_007));
    return false;
    wp_logout();
  }
  if($type != '1' && $type != '2'){
  ////  echo "<script>console.log('Failed on Type');</script>";
    die('You are not authorised to access this file.');
    return false;
    wp_logout();
  }
  return true;
}
function shgdprdm_isChecked($groupName,$value){
  $prefix = '';
  if($groupName == 'mdv-search-extra-group'){$prefix = 'mdv-incl-';}
  if(!empty($_POST[$groupName])){
      foreach($_POST[$groupName] as $groupItemVal){
          if($groupItemVal == $prefix.$value){
              return true;
          }
      }
  }
  return false;
}



if( SHGDPRDM_TESTING){
  $shgdprdm_type = 6; // Testing incorrect type
}


function shgdprdm_extSearch($shgdprdm_type, $shgdprdm_value) {

  // Validate Inputs before progressing

  if( shgdprdm_validateSearchInputs( $shgdprdm_type, $shgdprdm_value ) ) {
////  echo "<script>console.log('Passed All Search Validations');</script>";
  $result = shgdprdm_search($shgdprdm_type,$shgdprdm_value);

    if (shgdprdm_getOptionsGroup('shgdprdm_admin_plugins_settings_group')) {
    ////  echo '<script>console.log("Plugin Settings Options Group OK");</script>';
      //
      if( get_option('Woo-Commerce-Guest-Accounts') ){
        $wcfParam = $shgdprdm_value ;
        if(is_numeric($shgdprdm_value)){
          $wcfParam = get_user_by( 'id', $shgdprdm_value )->user_email;
        }
        $orders = new SHGdprdm_WCF($wcfParam);
        // echo $orders->display_orders();
        if(!$result && $orders){
          $userDetail = (object) array('Name' => 'WooCommerce Guest Customer', 'User Name' => 'Guest', 'Email' => $shgdprdm_value, 'Registration Date' => 'Guest', 'ID' => 'Guest');
          $result['userDetails'][0] = $userDetail;
          $result['tableDetails']['shgdprdm_tableCount'] = 1;
          $result['tableDetails']['shgdprdm_dataTableCount'] = 1;
          $result['tableDetails']['WooCommerce'] = $orders->shgdprdm_wcfAddOrders();
        }
        else if($result && $orders){ // //if there is result & there is orders (add orders to result)
          foreach($orders as $order){
            if(!$order->get_user()->ID){
              $name = 'WooCommerce Guest Customer';
              $userName = 'Guest';
              $regDate = 'Guest';
              $UserID = 'Guest';

            }
            else{
              // $user = get_user_by( 'email', $wcfParam );
              $name = $result['userDetails'][0]['Name'];
              $userName = $result['userDetails'][0]['User Name'];
              $regDate = $result['userDetails'][0]['Registration Date'];
              $UserID = $result['userDetails'][0]['ID'];
            }
            $userDetail = (object) array('Name' => $name, 'User Name' => $userName, 'Email' => $wcfParam, 'Registration Date' => $regDate, 'ID' => $UserID);
            array_push( $result['userDetails'], $userDetail);
          }

          $result['tableDetails']['WooCommerce'] = $orders->shgdprdm_wcfAddOrders();
        }
        elseif(!$result && !$orders){ // return error
          return false;
        }
        else{ //if there is result & there is no orders
          $result = $result;
        }

      }
    }


    if($result){
      // SH NOTES: ENCRYPT THIS?
      $recordCount = count($result['userDetails']);
      ////echo "<script>console.log('Record Count: ".$recordCount."');</script>";
      $notice = true;
      if($recordCount === 1){
        update_option('shgdprdm_admin_msg',array('class' => 'success', 'msg' => msg_001));
      }
      elseif($recordCount > 1){
      }
      elseif($recordCount < 1){
        update_option('shgdprdm_admin_msg',array('class' => 'warning', 'msg' => war_001));
      }
      else{
        update_option('shgdprdm_admin_msg', '');
      }

      return $result;

    }
    else{
      ////echo "<script>console.log('Failed on Search Function');</script>";
      exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
      die('Search Function Failed');
    }
  }
  else{
    /// echo "<script>console.log('Failed on Search Validation');</script>";
    exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
    die('Validation Function Failed');
  }

} /// end of ext search function




?>
