<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

// Array of all columns in all tables with their associated column name
// that relates to the UserID
function shgdprdm_getAllIdColumnNames($wpCore = TRUE, $corePosts = NULL, $wooComSearch = NULL, $eddSearch = NULL){
  $data = array();
  // $data['woocommerce'] = array();
  // $data['eddCore'] = array();

  if($wpCore){
    // echo "<br>In WP CORE LIST<br>";
    $data['core'] = array(
        'commentmeta' => NULL,
        'comments' => 'user_id',
        'links' => NULL,
        'options' => NULL,
        // 'postmeta' => 'ID',
        'postmeta' => NULL,
        // 'posts' => 'post_author',
        'posts' => NULL,
        'termmeta' => NULL,
        'terms' => NULL,
        'term_relationships' => NULL,
        'term_taxonomy' => NULL,
        'usermeta' => 'user_id',
        'users' => 'ID',
    );

  }
  if($corePosts){
    $data['corePosts'] = array(
      'posts' => 'ID',
      'postmeta' => NULL,
    );
  }
  if($wpCore && $wooComSearch){
    $data['woocommerce'] = array(
          'woocommerce_api_keys' => 'user_id',
          'woocommerce_attribute_taxonomies' => NULL,
          'woocommerce_downloadable_product_permissions' => 'user_id',
          'woocommerce_log' => NULL,
          'woocommerce_order_itemmeta' => NULL,
          'woocommerce_order_items' => NULL,
          'woocommerce_payment_tokenmeta' => NULL,
          'woocommerce_payment_tokens' => 'user_id',
          'woocommerce_sessions' => NULL, // SH Notes: There may be identifiable data here
          'woocommerce_shipping_zone_locations' => NULL,
          'woocommerce_shipping_zone_methods' => NULL,
          'woocommerce_shipping_zones' => NULL,
          'woocommerce_tax_rate_locations' => NULL,
          'woocommerce_tax_rates' => NULL,
        );
  }
  if(!$wpCore && $wooComSearch){

    $data['woocommerceCore'] = array(
          'postmeta' => NULL,
          'posts' => 'ID',
        );
  }

  if($wpCore && $eddSearch){
    $data['edd'] = array(
        'edd_customermeta' => NULL,
        'edd_customers' => 'user_id',
        'edd_license_activations' => NULL,
        'edd_licensemeta' => NULL,
        'edd_licenses' => NULL,
        'edd_subscriptions' => NULL
      );
  }
  if(!$wpCore && $eddSearch){
    $data['eddCore'] = array(
          'postmeta' => NULL,
          'posts' => 'ID',
        );
    $data['eddGuest'] = array(
          'edd_customers' => 'email',
        );
  }

  return $data;
}

// Get a list of column names for each table that are excluded from the over-write process
function shgdprdm_getAllExcludedColumnNames(){
  $data = array(

    'core' => array(
      'commentmeta' => NULL,
      'comments' => array('comments_post_ID','comment_date','comment_date_gmt','comment_approved','comment_type', 'comment_parent'),
      'links' => NULL,
      'options' => NULL,
      'postmeta' => array('post_id','meta_key'),
      //'posts' => array('ID','post_author','post_date','post_date_gmt','post_status','comment_status','ping_status','ping_status','post_password'),

      'posts' => array('ID','post_author','post_date','post_date_gmt','post_status','comment_status','ping_status','ping_status','post_password','post_name','to_ping','pinged','post_modified','post_modified_gmt','post_content_filtered','post_parent','guid','menu_order','post_type','post_mime_type','comment_count'),
      'termmeta' => NULL,
      'terms' => NULL,
      'term_relationships' => NULL,
      'term_taxonomy' => NULL,
      'usermeta' => 'meta_key',
      'users' => NULL),

      'woocommerce' => array(
        'woocommerce_api_keys' => NULL,
        'woocommerce_attribute_taxonomies' => NULL,
        'woocommerce_downloadable_product_permissions' => array('download_id','product_id',
                'order_id','order_key','downloads_remaining','access_granted','access_expires',
                'download_count'),
        'woocommerce_log' => NULL,
        'woocommerce_order_itemmeta' => array('meta_key'),
        'woocommerce_order_items' => NULL,
        'woocommerce_payment_tokenmeta' => NULL,
        'woocommerce_payment_tokens' => array('gateway_id','token','type','is_default'),
        'woocommerce_sessions' => NULL, // SH Notes: There may be identifiable data here
        'woocommerce_shipping_zone_locations' => NULL,
        'woocommerce_shipping_zone_methods' => NULL,
        'woocommerce_shipping_zones' => NULL,
        'woocommerce_tax_rate_locations' => NULL,
        'woocommerce_tax_rates' => NULL
      ),

      'woocommerceCore' => array(
        'postmeta' => array('post_id','meta_key'),
        'posts' => array('ID','post_author','post_date','post_date_gmt','post_status','comment_status','ping_status','ping_status','post_password','post_name','to_ping','pinged','post_modified','post_modified_gmt','post_content_filtered','post_parent','guid','menu_order','post_type','post_mime_type','comment_count'),
      ),

      'eddCore' => array(
        'postmeta' => array('post_id','meta_key'),
        'posts' => array('ID','post_author','post_date','post_date_gmt','post_status','comment_status','ping_status','ping_status','post_password','post_name','to_ping','pinged','post_modified','post_modified_gmt','post_content_filtered','post_parent','guid','menu_order','post_type','post_mime_type','comment_count'),
      )
  );
  return $data;
}

// Get a list of column names for each table that are INCLUDED IN the over-write process
function shgdprdm_getAllIncludedColumnNames(){
  $data = array(

    'core' => array(
      'commentmeta' => NULL,
      'comments' =>  NULL,
      'links' => NULL,
      'options' => NULL,
      'postmeta' => NULL,
      //'posts' => array('ID','post_author','post_date','post_date_gmt','post_status','comment_status','ping_status','ping_status','post_password'),

      'posts' => array('post_content','post_title','post_excerpt'),
      'termmeta' => NULL,
      'terms' => NULL,
      'term_relationships' => NULL,
      'term_taxonomy' => NULL,
      // 'usermeta' => array('meta_value'),
      // 'users' => array('user_login','user_pass','user_nicename','user_email','user_url','user_registered','user_activation_key','user_status','display_name')
      'usermeta' => NULL,
      'users' => NULL,
    ),

      'woocommerce' => array(
        'woocommerce_api_keys' => array('description','permissions','consumer_key','consumer_secret','nonces','truncated_key','last_access'),
        'woocommerce_attribute_taxonomies' => NULL,
        'woocommerce_downloadable_product_permissions' => array('user_email'),
        'woocommerce_log' => NULL,
        'woocommerce_order_itemmeta' => NULL,
        'woocommerce_order_items' => NULL,
        'woocommerce_payment_tokenmeta' => NULL,
        'woocommerce_payment_tokens' => array('gateway_id','token','type','is_default',),
        'woocommerce_sessions' => NULL, // SH Notes: There may be identifiable data here
        'woocommerce_shipping_zone_locations' => NULL,
        'woocommerce_shipping_zone_methods' => NULL,
        'woocommerce_shipping_zones' => NULL,
        'woocommerce_tax_rate_locations' => NULL,
        'woocommerce_tax_rates' => NULL
      ),

      'woocommerceCore' => array(
        'postmeta' => NULL,
        'posts' => array('post_content','post_title','post_excerpt'),
      ),

      'edd' => array(
        'edd_customermeta' => NULL,
        'edd_customers' => array('email','name'),
        'edd_license_activations' => array('site_name'),
        'edd_licensemeta' => NULL,
        'edd_licenses' => NULL,
        'edd_subscriptions' => NULL
      ),

      'eddCore' => array(
        'postmeta' => NULL,
        'posts' => array('post_content','post_title','post_excerpt'),
      )

      );
  return $data;
}


// Array of all columns in all tables with their associated column name
// that relates to the UserID
function shgdprdm_getExceptionColumnNames($wooComSearch = NULL){
  $data = array();

  $data['core'] = array(
        'commentmeta' => 'n/a',
        'comments' => 'user_id',
        'links' => NULL,
        'options' => NULL,
        'postmeta' => 'n/a',
        'posts' => 'post_author',
        'termmeta' => NULL,
        'terms' => NULL,
        'term_relationships' => NULL,
        'term_taxonomy' => NULL,
        'usermeta' => 'user_id',
        'users' => 'ID',
  );

  if($wooComSearch){
    $data['woocommerce'] = array(
          'woocommerce_api_keys' => 'user_id',
          'woocommerce_attribute_taxonomies' => NULL,
          'woocommerce_downloadable_product_permissions' => 'user_id',
          'woocommerce_log' => NULL,
          'woocommerce_order_itemmeta' => NULL,
          'woocommerce_order_items' => NULL,
          'woocommerce_payment_tokenmeta' => 'n/a',
          'woocommerce_payment_tokens' => 'user_id',
          'woocommerce_sessions' => NULL, // SH Notes: There may be identifiable data here
          'woocommerce_shipping_zone_locations' => NULL,
          'woocommerce_shipping_zone_methods' => NULL,
          'woocommerce_shipping_zones' => NULL,
          'woocommerce_tax_rate_locations' => NULL,
          'woocommerce_tax_rates' => NULL,
        );

    $data['woocommerceCore'] = array(
          'postmeta' => NULL,
          'posts' => 'ID',
        );

    $data['eddCore'] = array(
          'postmeta' => NULL,
          'posts' => 'ID',
        );
  }

  return $data;
}


// Get the ID reference from a given table name
function shgdprdm_getIdColumnNames($tableName, $wooComSearch, $eddSearch){
  $reference = NULL;
  $allNames = shgdprdm_getAllIdColumnNames($wooComSearch, $eddSearch);
    foreach($allNames as $table => $idRef){
      if($tableName == $table){
        $reference = $idRef;
      }
    }

  return $reference;
}

// Get the Column Names for any excluded columns from a given table name
function shgdprdm_getExludedColumnNames($tableName){
  $reference = NULL;
  $allNames = shgdprdm_getAllExcludedColumnNames();
  foreach($allNames as $table => $exclRef){
    if($tableName == $table){
      $excluded = $exclRef;
    }
  }
  return $excluded;
}
// Get the Column Names for any INCLUDED columns from a given table name
function shgdprdm_getIncludedColumnNames($tableName){
  $reference = NULL;
  $allNames = shgdprdm_getAllIncludedColumnNames();
  foreach($allNames as $table => $inclRef){
    if($tableName == $table){
      $included = $inclRef;
    }
  }
  return $included;
}
// Function to generate the returned data display
// Passed Args = Users Data: Unserialised & Decoded
function shgdprdm_displayDetails($data){
  // Confirm Args is an array
  if(!is_array($data)){
    return false;
    // return "Failed @ !is_array(data)";
  }
  // Confirm Args has data to itterate over
  if( count($data) < 1){
    return false;
    // return "Failed @ count(data) < 1";

  }
  // Confirm Args has data to itterate over (sublevel)
  $keysZero = array_keys($data);
  $zeroKey = $keysZero[0];
  $dataZero = $data[$zeroKey];
  if(!$dataZero ){
    return false;
    // return "Failed @ !dataZero";
  }
  // Confirm Args (sub level) is an object
  if( !is_object( $dataZero ) ){
    return false;
    // return "Failed @ !is_object";
  }
  // Confirm Object has data to itterate over
  if( is_object( $dataZero ) ){
    $dataObjects = get_object_vars ( $dataZero );
    $dataObjectsKeys = array_keys($dataObjects);
    $dataObjectsKeysZero = $dataObjectsKeys[0];
    if(!$dataZero->$dataObjectsKeysZero ){
      return false;
    }
  }

  $output = array();
  $outputDisplay = '';
  $detailsCount = 0;
  $detailsTotal = count($data);
  $dataPoints = 0;
  foreach($data as $col => $details){
    $outputDisplay .= "<strong>Record ".$detailsCount."</strong>:{";
    foreach($details as $name => $contents){

      if(is_array($contents)){
        $dataPoints+=count($contents);
        $outputDisplay .= "<br>&nbsp;<strong><em>".esc_html($name).":</em></strong>{ ";
        foreach($contents as $label => $contentData){
          if(is_array( $contentData )){
            // echo "Debug: ".$label." => ".json_encode($contentData);
            $outputDisplay .= '<strong>'.esc_html($label).':</strong>'.esc_html(serialize($contentData)).', ';
          }
          else{
            // echo "Debug: ".$label." => ".$contentData;
            $outputDisplay .= '<strong>'.esc_html($label).':</strong>'.esc_html($contentData).', ';
          }
        }
        $outputDisplay = substr($outputDisplay,0,-2);
        $outputDisplay .= " }";
      }
      else{
        $dataPoints++;
        $outputDisplay .= $name.":".esc_html($contents).", ";
        // $outputDisplay .= $name.":".htmlentities($contents).", ";
      }
    }
    $outputDisplay .= " }";
    if($detailsCount < $detailsTotal-1){
      $outputDisplay .= " ,<br>";
    }
    else{
      $outputDisplay .= "<br>";
    }
    $detailsCount++;
  }
  $output['display'] = $outputDisplay;
  $output['rows'] = $detailsTotal;
  $output['dataPoints'] = $dataPoints;
  return $output;
}


// function shgdprdm_getDataRecordCount($data){
//   foreach($data){}
// }

  function shgdprdm_makeInputColsArray($data){
    $colsOut = array();
    foreach($data as $item => $details){
      $colsOut[$item] = $details[0];
    }
    return $colsOut;
  }

  function shgdprdm_makeInputDataArray($cols, $data){
    $colsArray = shgdprdm_makeInputColsArray($cols);
    $dataOut = array();
    if(count($colsArray) == count($data)){
      foreach($colsArray as $item => $col){
        $dataOut[$col] = $data[$item];
      }
    }
    return $dataOut;
  }

  //function to check if a submitted value is numeric and telephone compatible (ignoring spaces, plus symbols("+") & minus-symbols/dashes ("-") )
  function shgdprdm_validatePhone($phoneNumber){
    if($phoneNumber[0] == '+'){
      $phoneNumber = substring($phoneNumber,1);
    }
    if( preg_match( '/[^0-9-()]/', str_replace(' ', '', $phoneNumber) ) ){
      return false;
    }
    return true;
  }

  function shgdprdm_validateReturnedSearchData($postData){
    // Expected (4):
    // shgdprdmrd_nonce
    // _wp_http_referer
    // data
    // search-return (Boolean True or False)
    // return-submit
    $expected = array('shgdprdmrd_nonce','_wp_http_referer','data','search-return');
    if(count($postData) != 4){
      // echo "<br> Failed on Count";
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' <em>error ref: HPR_001</em>'));
      return FALSE;
    }
    foreach($postData as $postName => $postVal){
      if(!in_array($postName,$expected)){
        // echo "<br> Failed on Variables";
        update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' <em>error ref: HPR_002</em>'));
        return FALSE;
      }
    }
    if( empty($postData['data'])){
      // echo "<br> Failed on No DATA";
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' <em>error ref: HPR_003</em>'));
      return FALSE;
    }

    // This must be the final validation
    $searchReturn = sanitize_text_field( $postData['search-return'] );
    if( $searchReturn === TRUE || intval($searchReturn) === 1 ) {
      // echo "<br> Success on Search Return";
      // echo "<br> Val: ".intval($postData['search-return']);
      return TRUE;
    }
    else{
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_009.' <em>error ref: HPR_004</em>'));
      return FALSE;
    }
    return TRUE;
  }

  function shgdprdm_validateReturnedSearchDataNonce($nonceVal){
    // $nonceVal is passed in already passed through sanitize_text_field

    // Verify Data exists
    if( !$nonceVal || empty($nonceVal) ){
      return FALSE;
    }
    // Validate Expected Nonce
    if( !wp_verify_nonce( $nonceVal,
        esc_url( admin_url( 'admin.php').'?page=seahorse_gdpr_data_manager_plugin') )
    ){
      return FALSE;
    }
    // Validate Admin Referer
    if(! check_admin_referer( esc_url( admin_url( 'admin.php').'?page=seahorse_gdpr_data_manager_plugin'),
    'shgdprdmrd_nonce' )){
      return FALSE;
    }
    return TRUE;
  }

  function shgdprdm_validateReturnedSearchDataUser(){
    // Validate that user is admin
    if( !current_user_can('administrator') ){
      return FALSE;
    }
    // Validate User Admin Privledges
    if( !current_user_can('manage_options') ){
      return FALSE;
    }
    return TRUE;
  }

  function shgdprdm_extractReturnedSearchData($postData){
    // $data = unserialize( base64_decode( $postData['data'] ) );
    $data = unserialize( base64_decode( $postData ) );
    // $data = sanitize_text_field( $data );
    return $data;
  }

  function shgdprdm_validateExtractedSearchData($extractedSearchData){
    // Expected ( array(1) ):
    // userDetails (array of objects)
    // 'Name'
    // 'User Name'
    // 'Email'
    // 'Registration Date'
    // 'ID'
    if( !is_array( $extractedSearchData ) ){
      // echo "<br> Failed on DATA Not Array";
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.1'));
      return FALSE;
    }
    if(count($extractedSearchData) < 1){
      // echo "<br> Failed on DATA - Empty Array";
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.2'));
      return FALSE;
    }
    if( !isset( $extractedSearchData['userDetails'] ) ){
      // echo "<br> Failed on DATA - No User Data";
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.3'));
      return FALSE;
    }
    if( !is_array( $extractedSearchData['userDetails'] ) ){
      // echo "<br> Failed on DATA - No Array  User Data";
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.4'));
      return FALSE;
    }
    if( empty( $extractedSearchData['userDetails'] ) ){
      // echo "<br> Failed on DATA - Empty User Data";
      update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.5'));
      return FALSE;
    }
    $expected = array('Name','User Name', 'Email', 'Registration Date','ID');
    $userCounter = 0;
    $valuesCounter = 0;
    foreach($extractedSearchData['userDetails'] as $dataItem){

      if( !is_object($dataItem) ){
        update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.6.'.$userCounter));
        return FALSE;
      }
      foreach($dataItem as $dataCol => $dataDetail){
        if(!in_array( $dataCol, $expected )){
          update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.6.'.$userCounter.'.'.$valuesCounter));
          return FALSE;
        }
        if($dataCol == 'Email'){
          $dataDetail = sanitize_email( $dataDetail );
          if(!is_email($dataDetail)){
            update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.7.'.$userCounter));
            return FALSE;
          }
        }
        if($dataCol == 'ID'){
          $dataDetail = sanitize_text_field( $dataDetail );
          if( !is_numeric($dataDetail) ){
            if( strtoupper($dataDetail) != 'GUEST' ){
              update_option('shgdprdm_admin_msg',array('class' => 'error', 'msg' => SHGDPRDM_err_010.'<br>Error Code 2.8.'.$userCounter));
              return FALSE;
            }
          }
        }
        $valuesCounter++;
      }
      $userCounter++;
    }
    return TRUE;
  }

  function shgdprdm_getReExportNotice( $recordRef ){
    $html = '';
    if( $recordRef ){
      $html .= '
      <div class="shgdprdm-notice shgdprdm-error"> <div class="shgdprdm-complete-sync-warning-container"><span class="shgdprdm_icon-xl dashicons shgdprdm-complete-sync-warning shgdprdm-not-valid dashicons-warning"></span>
      <p>Please complete Delete action to fully Restore / Synchronise this Record</p>
      </div>
      </div>';
    }
    return $html;
  }

  function shgdprdm_isReDelete($delete = TRUE){
    if( get_option('shgdprdm_sync_delete') !== null &&
      ( isset(get_option('shgdprdm_sync_delete')['redelete']) && get_option('shgdprdm_sync_delete')['redelete'] != '' ) &&
      ( isset(get_option('shgdprdm_sync_delete')['dRef']) && get_option('shgdprdm_sync_delete')['dRef'] != '' )
    ){
      $return = get_option('shgdprdm_sync_delete')['dRef'];

      if($delete){
        update_option('shgdprdm_sync_delete', '');
      }
      return $return;
    }
    update_option('shgdprdm_sync_delete', '');
    return FALSE;
  }

  function shgdprdm_getUserIdEmail( $recordRef, $searchBy ){
    if(!$recordRef){
      return FALSE;
    }
    if( !is_email($recordRef) && !is_numeric($recordRef) ){
      return FALSE;
    }
    if( $searchBy == '1' && is_email($recordRef) ){
      return $recordRef;
    }
    if( $searchBy == '2' && is_numeric($recordRef) ){
      return $recordRef;
    }
    if( $searchBy == '1' && is_numeric($recordRef) ){
      $user = get_user_by('id',$recordRef);
      if($user){
        if($user->user_email){
          return $user->user_email;
        }
      }
      return FALSE;
    }
    if( $searchBy == '2' && is_email($recordRef) ){
      $user = get_user_by('email',$recordRef);
      if($user){
        if($user->ID){
          return $user->ID;
        }
      }
      return FALSE;
    }
    return FALSE;
  }

?>
