<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
if(!class_exists('SHGdprdm_MSF')){
  class SHGdprdm_MSF {

  protected $usby;
  protected $uid;
  protected $ueml;
  protected $uadm;
  protected $postIds;
  protected $commentIds;
  protected $dataTableNames;
  protected $pluginDir;

	/** Class constructor */
	public function __construct() {

    if(count(func_get_args()) !== 1){
      throw new Exception('Error! Action cannot be performed.');
    }
    $this->usby = func_get_args()[0];

    if ( method_exists($this, 'shgdprdm_getUserId' ) ) {
      $this->uid = self::shgdprdm_getUserId();
    }
    else{
      $this->uid = FALSE;
    }
    if ( method_exists($this, 'shgdprdm_getUserEmail' ) ) {
      $this->ueml = self::shgdprdm_getUserEmail();
    }
    else{
      $this->ueml = FALSE;
    }
    if ( method_exists($this, 'shgdprdm_isUserAdmin' ) ) {
      $this->uadm = self::shgdprdm_isUserAdmin();
    }
    else{
      // Failsafe
      $this->uadm = TRUE;
    }


    $this->postIDarray = array();
    if ( !$this->uadm && method_exists($this, 'shgdprdm_getPostIds' ) ) {
      $this->shgdprdm_getPostIds();
    }
    $this->commentIDarray = array();
    if ( !$this->uadm && method_exists($this, 'shgdprdm_getCommentIds' ) ) {
      self::shgdprdm_getCommentIds();
    }
    $this->dataTableNames = array();

    global $seahorseGdprDataManagerPlugin;
    if($seahorseGdprDataManagerPlugin){
      $this->pluginDir = $seahorseGdprDataManagerPlugin->shgdprdm_getPluginDir();
    }
    else{
      global $seahorseMyDataViewExternalAccessRequest;
      $this->pluginDir = $seahorseMyDataViewExternalAccessRequest->shgdprdm_getPluginDir();
    }

	}

  /**
  * Run Full Search
  *
  * @return null|array
  */
  public function shgdprdm_getDisplay() {
    require_once $this->pluginDir.'inc/shgdprdm_helpers.php';
    $userDetails = 'Test Search Class';

    $tableCount = 0;
    $dataTableCount = 0;
    $dataTableNames = array();
    $tableDetails = array();

    // Itterate through all tables to find User Data
    // echo "<br>Woo Comms Option: <br>";
    // print_r(get_option('Woo-Commerce-Plugin')['Woo-Commerce-Plugin']);
    // echo "<br>";
    $tableListCore = shgdprdm_getAllIdColumnNames( TRUE , FALSE, ( class_exists('SHGdprdm_WCF') && isset( get_option('Woo-Commerce-Plugin')['Woo-Commerce-Plugin'] ) )?TRUE:FALSE , (class_exists('SHGdprdm_EDDF') && isset( get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin'] ) )?TRUE:FALSE  );
    $tableListCorePosts = shgdprdm_getAllIdColumnNames( FALSE , TRUE, FALSE , FALSE );
    // Only Carry-out if there is a UserID
    // echo "<br>Table List:<br>";
    // print_r($tableListCore);
    // echo "<br>";
    if($this->uid && !$this->uadm){
      // echo "THIS SEARCHED USER IS NOT AN ADMIN";
      foreach($tableListCore as $cat => $tblData){
        $tableCount+=count($tblData);
        // echo "Display Data 1<br>";
        $display = $this->shgdprdm_getDisplayData($tblData);
          foreach($display['tableNameWithData'] as $tblName){
            if(isset($tableDetails[$tblName])){
              $display[$tblName] = array_merge($tableDetails[$tblName], $display[$tblName]);
            }
            else{
              $dataTableCount ++;
              array_push($dataTableNames,$tblName);
            }
          }
          unset($display['tableWithData']);
          unset($display['tableNameWithData']);
          $tableDetails = array_merge($tableDetails, $display);
      }

      // Get Posts
      // echo "<br>Getting Posts<br>";
      // print_r($this->postIDarray);
      // echo "<br>";

      if($this->postIDarray){
        $condition = " IN ( ";
        foreach($this->postIDarray as $pid){
          $condition.= $pid. ',';
        }
        $condition = substr($condition, 0 , -1);
        $condition.= ') ';
        // echo "Display Data 1.1<br>";
        $display = self::shgdprdm_getDisplayData($tableListCorePosts['corePosts'], $condition);
        // echo "Display: <br";
        // print_r($display);
        foreach($display['tableNameWithData'] as $tblName){
          if(isset($tableDetails[$tblName])){
            $display[$tblName] = array_merge($tableDetails[$tblName], $display[$tblName]);
          }
          else{
            $dataTableCount ++;
            array_push($dataTableNames,$tblName);
          }
        }
        unset($display['tableWithData']);
        unset($display['tableNameWithData']);
        $tableDetails = array_merge($tableDetails, $display);
      }
      else{
        foreach($tableListCorePosts['corePosts'] as $tName => $tRef){
          if($tRef){
            $dataTableCount ++;
            $tableDetails = array_merge($tableDetails, array( $tName => array() ) );
            // array_push($dataTableNames,$tName);
          }
        }
      }



    }
    // Otherwise Just Count the tables
    else{
      foreach($tableListCore as $cat => $tblData){
        $tableCount+=count($tblData);
      }
    }




  $hasOrders = null;
  if(isset(get_option('Woo-Commerce-Plugin')['Woo-Commerce-Plugin'])) {
    if(class_exists('SHGdprdm_WCF')){
      $tableListWC = shgdprdm_getAllIdColumnNames( FALSE, FALSE, TRUE, FALSE );
      // echo "<br>Checking for Woo Commerce DAta<br>";
      // Get Woo Commerce Order Data
      $orders = new SHGdprdm_WCF($this->ueml);
      $hasOrders = $orders->hasOrders();
      if($hasOrders){
        $tableCount++;
        $dataTableCount ++;
        array_push($dataTableNames,'WooCommerce');
        $tableDetails['WooCommerce'] = $orders->shgdprdm_wcfAddOrders();
        $oids = $orders->getIDS();
        if($oids){
          $condition = " IN ( ";
          foreach($oids as $oid){
            $condition.= $oid. ',';
            array_push($this->postIDarray,$oid);
          }
          $condition = substr($condition, 0 , -1);
          $condition.= ') ';
          // echo "Display Data 2<br>";
          // $displayCore = array();
          // $displayWC = array();
          $displayCoreWC = array();
          $displayCoreWC = array_merge($tableListCore['woocommerce'],$tableListWC['woocommerceCore']);
          // $displayCore = self::shgdprdm_getDisplayData($tableListCore['woocommerce'], $condition);
          // $displayWC = self::shgdprdm_getDisplayData($tableListWC['woocommerceCore'], $condition);
          // $display = array_merge($displayCore,$displayWC);
          $display = self::shgdprdm_getDisplayData($displayCoreWC, $condition);
          foreach($display['tableNameWithData'] as $tblName){
            if(isset($tableDetails[$tblName])){
              $display[$tblName] = array_merge($tableDetails[$tblName], $display[$tblName]);
            }
            else{
              $dataTableCount ++;
              array_push($dataTableNames,$tblName);
            }
          }
          unset($display['tableWithData']);
          unset($display['tableNameWithData']);
          $tableDetails = array_merge($tableDetails, $display);
        }
      }
  
      // Only Search USer Specific Item if there is a UserID
      if($this->uid){
        // Payment Tokens Meta Data
        $paymentTokens = (class_exists('WC_Payment_Tokens') ? WC_Payment_Tokens::get_customer_tokens( $this->uid ) : array() );
        if(count($paymentTokens) > 0){
          $tokensMetaData = array();
          foreach($paymentTokens as $token){
            $tokenMeta = (object)array(
              'last4' => $token->get_last4(),
              'expiry_year' => $token->get_expiry_year(),
              'expiry_month' => $token->get_expiry_month(),
              'card_type' => $token->get_card_type(),
            );
            array_push($tokensMetaData,$tokenMeta);
          }
          $dataTableCount ++;
          array_push($dataTableNames,'woocommerce_payment_tokenmeta');
          $tableDetails = array_merge($tableDetails, array('woocommerce_payment_tokenmeta' => $tokensMetaData));
        }
      }
    } // End: If Calss Exists (Woo Coms)
  } //End: If Option for WC is selected
  
  $hasDownloads = null;
  if( isset(get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin'])) {

    if( class_exists('SHGdprdm_EDDF') ){
      
      $tableList = shgdprdm_getAllIdColumnNames( FALSE, FALSE, FALSE, TRUE );
      foreach($tableList as $cat => $tblData){
        $tableCount+=count($tblData);
        // echo "Display Data 3<br>";
        $display = self::shgdprdm_getDisplayData($tblData);
        foreach($display['tableNameWithData'] as $tblName){
          if(!empty($tableDetails[$tblName]) ){
            $display[$tblName] = array_merge($tableDetails[$tblName], !empty($display[$tblName])?$display[$tblName]:array());
          }
          else{
            $dataTableCount ++;
            array_push($dataTableNames,$tblName);
          }
        }
        unset($display['tableWithData']);
        unset($display['tableNameWithData']);
        $tableDetails = array_merge($tableDetails, $display);
      }

      // Get Easy Digital Downloads Data
      $downloads = new SHGdprdm_EDDF($this->ueml);
      $payments = $downloads->edd_returnPurch();
      $hasDownloads = $downloads->hasDownloads();
      $isEddCustomer = $downloads->edd_returnCust();
      // echo "<br>I SEDD CUST: <br>";
      // print_r($isEddCustomer);
      if($isEddCustomer){
        // echo "IS CUSTOMER";
        $tableCount++;
        $dataTableCount ++;
        array_push($dataTableNames,'EasyDigitalDownloads');
        $tableDetails['EasyDigitalDownloads'] = $downloads->shgdprdm_eddAddPurchases();
        $dids = $downloads->getIDS();
        $logs = $downloads->getLogsIDS();
        // echo "<br>DIDS: <br>";
        // print_r($dids);
        if($dids){
          $condition = " IN ( ";
          if(!is_array($dids)){
            $dids = explode(',',$dids);
          }
          // echo "<br>DIDS: <br>";
          // print_r($dids);
          // echo "<br>";
          // print_r($this->postIDarray);
          // print_r($tableDetails);
          
          // $postIDVals = array_values($this->postIDarray);
          foreach($dids as $dix => $did){
            $condition.= $did. ',';
            if( !in_array( $did , $this->postIDarray) ){
              array_push($this->postIDarray,$did);
            }
            else{
              unset($dids[$dix]);
            }
          }
          // echo "<br>";
          // print_r($this->postIDarray);
          $condition = substr($condition, 0 , -1);
          $condition.= ') ';
          
          // If there are EDD Posts that havn't been already picked up
          if(!empty($dids)){
            // echo "Display Data 4<br>";
            $display = self::shgdprdm_getDisplayData($tableList['eddCore'], $condition);
            
            foreach($display['tableNameWithData'] as $tblName){
              if(!empty($tableDetails[$tblName]) ){
                $display[$tblName] = array_merge($tableDetails[$tblName], !empty($display[$tblName])?$display[$tblName]:array());
              }
              else{
                $dataTableCount ++;
                array_push($dataTableNames,$tblName);
              }
            }
            unset($display['tableWithData']);
            unset($display['tableNameWithData']);
            $tableDetails = array_merge($tableDetails, $display);
          }
        }
        
        // print_r($tableDetails);
        // If there are no downloads for this customer, however, they are still a customer on the system
        // OR 
        // If it is a Guest EDD Customer who does not have a user ID to scan the edd_customer table with
        if( !empty( $tableDetails['EasyDigitalDownloads'] ) && empty( $tableDetails['edd_customers'] ) ){
          $display = self::shgdprdm_getDisplayData($tableList['eddGuest'], " = '".$this->ueml."' ");
          foreach($display['tableNameWithData'] as $tblName){
            if(!empty($tableDetails[$tblName]) ){
              $display[$tblName] = array_merge($tableDetails[$tblName], !empty($display[$tblName])?$display[$tblName]:array());
            }
            else{
              $dataTableCount ++;
              array_push($dataTableNames,$tblName);
            }
          }
          unset($display['tableWithData']);
          unset($display['tableNameWithData']);
          $tableDetails = array_merge($tableDetails, $display);
        }
      }
    } // End: IF Calss Exists EDD
  } // End: If EDD Option is set

    // Post & Comment Meta-Data
    if( ($this->uid || $hasOrders || $isEddCustomer ) ){
      // echo "<br>IS A EDD CUSTOMER: ";
      // echo "<br>User ID : ".$this->uid;
      // Get Post Meta Data
      $postMeta = array();
      // echo "<br>PostID Array: <br>";
      // print_r($this->postIDarray);
      if(count($this->postIDarray) > 0){
        $tableCount++;
        foreach($this->postIDarray as $postID){
          $postMetaData = self::shgdprdm_getPostMeta($postID);
          if($postMetaData){
            array_push($postMeta,$postMetaData);
          }
        }
        $dataTableCount ++;
        array_push($dataTableNames,'postmeta');
        $tableDetails = array_merge($tableDetails, array('postmeta' => $postMeta));
      }
      
      // Get Comments Meta Data
      $commentMeta = array();
      if(count($this->commentIDarray) > 0){
        $tableCount++;
        foreach($this->commentIDarray as $commentID){
          $commentMetaData = self::shgdprdm_getCommentMeta($commentID);
          if($commentMetaData){
            array_push($commentMeta,$commentMetaData);
         }
        }
        $dataTableCount ++;
        array_push($dataTableNames,'commentmeta');
        $tableDetails = array_merge($tableDetails, array('commentmeta' => $commentMeta));
      }
    }

    $tableDetails['shgdprdm_tableCount'] = $tableCount;
    $tableDetails['shgdprdm_dataTableCount'] = $dataTableCount;

    ksort($tableDetails); // Reorder Alphabetically
    $this->dataTableNames = $dataTableNames;
    return $data = array('userDetails' => $userDetails, 'tableDetails' => $tableDetails);
  }

  /**
  * Run Full Search
  *
  * @return null|array
  */
  private function shgdprdm_getDisplayData($tblData,$ref = NULL, $exclude = NULL){
    $tableDetails = array();
    $tableWithData = 0;
    $tableNameWithData = array();

    if(!empty($tblData)){
      foreach($tblData as $tableName => $condition){
        if($condition){
          $searchData = self::shgdprdm_searchData($tableName,$condition,$ref);
          if(isset($tableDetails[$tableName])){
            $tableDetails[$tableName] = array_merge($tableDetails[$tableName],$searchData);
          }
          else{
            $tableWithData++;
            array_push($tableNameWithData,$tableName);
            $tableDetails[$tableName] = $searchData;
          }
        }
      }
    }
    $tableDetails['tableWithData'] = $tableWithData;
    $tableDetails['tableNameWithData'] = $tableNameWithData;
    return $tableDetails;
  }

  /**
  * Run Full Search
  *
  * @return null|array
  */
  private function shgdprdm_searchData($table,$condition,$ref=NULL) {
    global $wpdb;
    $select = '*';
    if($ref){
      $data = $wpdb->get_results("SELECT {$select} FROM {$wpdb->prefix}{$table} WHERE {$condition} {$ref}", OBJECT);
      return $data;
    }
    if($this->uid){
      $data = $wpdb->get_results("SELECT {$select} FROM {$wpdb->prefix}{$table} WHERE {$condition} = {$this->uid}", OBJECT);
      return $data;
    }
    return false;
  }

  /**
   * Run Posts Search
   *
   * @return null|array
   */
   private function shgdprdm_getPostIds() {
      if($this->uid && is_numeric($this->uid) ){
        $posts = get_posts(array('author' => $this->uid )); // This does not get all post types
        
        if(class_exists('SHGdprdm_EDDF')){
          $downloads = new SHGdprdm_EDDF($this->uid);
          $posts = $downloads->getAdvancedPosts( array('author' => $this->uid ) );
        }
        
        // $posts = $this->shgdprdm_get_posts(array('author' => $this->uid )); // Created Bespoke Function to workaround
        if ( $posts ) {
          foreach ( $posts as $userPost ){
            array_push($this->postIDarray,$userPost->ID);
          }
        }
        // echo "Posts: ";
        // print_r($posts);

        // If Easy Digital Downloads is not selected, then remove the Posts ID's that are Easy Digital Downloads Purchases
        if( !isset( get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin'] ) ){
          // Get Easy Digital Downloads Data
          if(class_exists('SHGdprdm_EDDF')){
            $downloads = new SHGdprdm_EDDF($this->ueml);
            $hasDownloads = $downloads->hasDownloads();
            $isEddCustomer = $downloads->edd_returnCust();
            $systemDownloadIds = array();
            $coreDownloadIds = array();
            $eddLogIds = array();
            $allEddIDs = array();

            if($isEddCustomer){
              $systemDownloadIds = $downloads->getIDS();
              $coreDownloadIds = $downloads->getPurchaseIdsExt();
              $eddLogIds = $downloads->getLogsIDS();
              if(!is_array($systemDownloadIds)){
                $systemDownloadIds = (array)$systemDownloadIds;
              }
              $allEddIds = array_merge($systemDownloadIds,$coreDownloadIds,$eddLogIds);
              if( !empty($allEddIds) && count($allEddIds) > 0 ){
                foreach($this->postIDarray as $postIDKey => $postIDVal){
                  if( in_array($postIDVal, $allEddIds) ){
                    unset($this->postIDarray[$postIDKey]);
                  }
                }
              }
            }
          } // End: If Class Exists
        }
      }
    }

    /**
    * Run Posts Meta Data
    *
    * @return null|array
    */
    private function shgdprdm_getPostMeta($postID) {
      $postMeta = get_post_meta( $postID );
      if(empty($postMeta)){
        $postMeta = NULL;
      }
      else{
        $postMetaArray = array('post_id' => $postID );
        foreach($postMeta as $pkey => $pVals){
          if(is_Array($pVals) && count($pVals) == 1 & isset($pVals[0])){
            $postMetaArray[$pkey] = $pVals[0];
          }
          else if(is_Array($pVals) && count($pVals) > 1){
            $valCount = 0;
            foreach($pVals as $key => $val){
              $postMetaArray[$pkey.'('.$valCount.')'] = $val;
              $valCount++;
            }
          }
        }
        $postMeta = (object)$postMetaArray;
      }
      return $postMeta;
    }

    /**
    * Run Comment Search
    *
    * @return null|array
    */
    private function shgdprdm_getCommentIds() {

      if($this->uid){
        $comments = get_comments(array('user_id' => $this->uid ));
        if ( $comments ) {
          foreach ( $comments as $comment ){
            array_push($this->commentIDarray,$comment->comment_ID);
          }
        }
      }
    }

    /**
    * Run Comment Meta Data
    *
    * @return null|array
    */
    private function shgdprdm_getCommentMeta($commentID) {
      $commentMeta = get_comment_meta( $commentID );
      if(empty($commentMeta)){
        $commentMeta = NULL;
      }
      else{
        $commentMetaArray = array('comment_id' => $commentID );

        foreach($commentMeta as $ckey => $cVals){
          if(is_Array($cVals) && count($cVals) == 1 & isset($cVals[0])){
            $commentMetaArray[$ckey] = $cVals[0];
          }
          else if(is_Array($cVals) && count($cVals) > 1){
            $valCount = 0;
            foreach($cVals as $key => $val){
              $commentMetaArray[$ckey.'('.$valCount.')'] = $val;
              $valCount++;
            }
          }
        }
        $commentMeta = (object)$commentMetaArray;
      }
      return $commentMeta;
    }

    /**
    * Get User ID
    *
    * @return null|int
    */
    private function shgdprdm_getUserId() {
      if(is_email($this->usby) ){
        $user = get_user_by( 'email', $this->usby );
        if($user){
          return $user->ID;
        }

      }
      else if(is_numeric($this->usby)){
        return $this->usby;
      }
      else{
        return null;
      }
    }

    /**
    * Get User Email
    *
    * @return null|int
    */
    private function shgdprdm_getUserEmail() {
      if(is_email($this->usby) ){
        return $this->usby;
      }
      else if(is_numeric($this->usby)){
        return get_user_by( 'id', $this->usby )->user_email;
      }
      else{
        return null;
      }
    }

    private function shgdprdm_isUserAdmin(){
      $checkUser = get_user_by( 'email', $this->ueml );
      if(user_can( $checkUser, 'administrator' )){
        return TRUE;
      }
      else if(user_can( $checkUser, 'manage_options' )){
        return TRUE;
      }
      else{
        return FALSE;
      }
    }

    private function shgdprdm_get_posts( $args_array ) {

      if(!is_array($args_array)){
        $searchCondition = 'post_author';
        $searchParam = $args_array;
      }
      else if( array_key_exists('author', $args_array ) ){
        $searchCondition = 'post_author';
        $searchParam = $args_array['author'];
      }
      // Could be expanded to search for other condtions eg date, title etc
      else{
        $searchCondition = 'post_author';
        $searchParam = get_current_user_id();
      }
      $select = '*';
      $table = 'posts';
      global $wpdb;

      $posts = $wpdb->get_results("SELECT {$select} FROM {$wpdb->prefix}{$table} WHERE {$searchCondition} = {$searchParam}", OBJECT);
      return $posts;
    }



  } // end of class
}

 ?>
