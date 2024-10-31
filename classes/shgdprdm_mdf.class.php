<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
if(!class_exists('SHGdprdm_MDF')){
  class SHGdprdm_MDF {

    protected $usby;
    protected $uid;
    protected $ueml;
    protected $postIds;
    protected $commentIds;
    protected $shgdprdm_option_user_deleted_text_content_comment;
    protected $shgdprdm_option_user_deleted_text_content_post;
    protected $shgdprdm_option_user_deleted_text_title_post;
    protected $shgdprdm_option_user_deleted_text_username;
    protected $shgdprdm_option_user_deleted_useremail;
    protected $shgdprdm_option_user_deleted_metadata;
    protected $shgdprdm_commentUpdateArray;
    protected $shgdprdm_postUpdateArray;
    protected $postsDeleted;
    protected $commentsDeleted;
    protected $wooComDeleted;
    protected $wooCommerceData;
    protected $easyDigiDlDeleted;
    protected $easyDigitalDownloadsData;
    protected $eddPurchaseIds;

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
      // Do not initiate the class if the user has admin capabilities
      if($this->uid && user_can( $this->uid, 'manage_options' )){
        throw new Exception('Error! Action cannot be performed by this user.');
        // die('Action Cannot be performed by this user');
      }

      if ( method_exists($this, 'shgdprdm_getUserEmail' ) ) {
        $this->ueml = self::shgdprdm_getUserEmail();
      }
      else{
        $this->ueml = FALSE;
      }
      $this->shgdprdm_option_user_deleted_text_content_comment = 'This Comment Has Been Deleted<br><br>via<br><a href="'.get_site_url().'" target="_blank">Seahorse: GDPR Data Manager - <em>GDPR/CCPA Compliance Plugin</em></a>';
      $this->shgdprdm_option_user_deleted_text_content_post = 'This Post Has Been Deleted<br><br>via<br><a href="'.get_site_url().'" target="_blank">Seahorse: GDPR Data Manager - <em>GDPR/CCPA Compliance Plugin</em></a>';

      $this->shgdprdm_option_user_deleted_text_title_post = 'Title Deleted';

      $this->shgdprdm_option_user_deleted_text_username = 'Deleted User (Ext test)';
      $this->shgdprdm_option_user_deleted_useremail = 'deleted@deleted.com';
      $this->shgdprdm_option_user_deleted_metadata = 'Deleted By User (Ext test)';

      $this->shgdprdm_commentUpdateArray = array();
      if ( method_exists($this, 'shgdprdm_makeEmptyCommentUpdateArray' ) ) {
        self::shgdprdm_makeEmptyCommentUpdateArray();
      }
      $this->shgdprdm_postUpdateArray = array();
      if ( method_exists($this, 'shgdprdm_makeEmptyPostUpdateArray' ) ) {
        self::shgdprdm_makeEmptyPostUpdateArray();
      }
      $this->postIDarray = array();
      if ( method_exists($this, 'shgdprdm_getPostIds' ) ) {
        self::shgdprdm_getPostIds();
      }
      $this->commentIDarray = array();
      if ( method_exists($this, 'shgdprdm_getCommentIds' ) ) {
        self::shgdprdm_getCommentIds();
      }

      // *** WOOCOMMERCE SPECIFIC
      $this->wooCommerceData = null;

      // This Function May not be used?
      if ( method_exists($this, 'shgdprdm_getWooCommerceData' ) ) {
        self::shgdprdm_getWooCommerceData();
      }
      // If WooCommerce is not selected, then remove the Posts ID's that are WooCommerce Orders
      if( !isset( get_option('Woo-Commerce-Plugin')['Woo-Commerce-Plugin'] ) ){
        if ( method_exists($this, 'shgdprdm_removeWooCommercePostIds' ) ) {
          self::shgdprdm_removeWooCommercePostIds();
        }
      }

      // *** Easy Digital Downloads SPECIFIC
      $this->easyDigitalDownloadsData = null;

      // This Function May not be used?
      if ( method_exists($this, 'shgdprdm_getEasyDigitalDownloadsData' ) ) {
        self::shgdprdm_getEasyDigitalDownloadsData();
      }
      // If Easy Digital Downloads is not selected, then remove the Posts ID's that are Easy Digital Downloads Purchases
      if( !isset( get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin'] ) ){
        if ( method_exists($this, 'shgdprdm_removeEasyDigitalDownloadsPostIds' ) ) {
          self::shgdprdm_removeEasyDigitalDownloadsPostIds();
        }
      }

      $this->postsDeleted = false;
      $this->commentsDeleted = false;
      $this->wooComDeleted = false;
      $this->easyDigiDlDeleted = false;
  	} // End of Constructor

    // *************************
    // Helpers
    // *************************

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
        else{
          return $this->usby;
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

    // Function to send an email to the user to confirm that delete occurred
    private function shgdprdm_emailDeletedUser() {
      $headers = 'From: ' . get_bloginfo( "name" ) . ' <' . get_bloginfo( "admin_email" ) . '>' . "\r\n";
      wp_mail( $this->ueml, 'Your Request for Account at ' . get_bloginfo("name") . ' to be Deleted has been processed.', $headers );
    }


    /**
    * Helper Function - Get all Posts by USerID
    * Workaround for "get_posts()" which seems to be causing problems based on post_type & post_status
    *
    * @return array
    */
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


    // *************************
    // Delete Posts & Posts Meta
    // *************************

    /**
    * Get the ID's of all of this User's Posts
    *
    * @return null|setter
    */
    private function shgdprdm_getPostIds() {
      global $post;
      if($this->uid && is_numeric($this->uid) ){
        // $posts = get_posts(array('author' => $this->uid )); // This does not get all post types
        $posts = $this->shgdprdm_get_posts(array('author' => $this->uid )); // Created Bespoke Function to workaround
        if ( $posts ) {
          foreach ( $posts as $userPost ){
            array_push($this->postIDarray,$userPost->ID);
          }
        }
      }

    }

    /**
    * Remove any Woo Commerce Order ID's from the Post ID's Array if Woo Commerce is not included in the search
    *
    * @return null|setter
    */
    private function shgdprdm_removeWooCommercePostIds() {
      if($this->wooCommerceData){
        $orderPostIds = $this->wooCommerceData->getIDS();
        // echo "ORDER POSTS: ".serialize($orderPostIds);
        if( count($orderPostIds) > 0 ){
          foreach($this->postIDarray as $postIDKey => $postIDVal){
            if( in_array($postIDVal,$orderPostIds) ){
              unset($this->postIDarray[$postIDKey]);
            }
          }
        }
      }
    }

    /**
    * Remove any Easy Digital Downloads Purchase ID's from the Post ID's Array if Easy Digital Downloads is not included in the search
    *
    * @return null|setter
    */
    private function shgdprdm_removeEasyDigitalDownloadsPostIds() {
      if($this->easyDigitalDownloadsData){
        $systemDownloadIds = array();
        $coreDownloadIds = array();
        $eddLogIds = array();
        $allEddIDs = array();

        $purchasePostIds = $this->easyDigitalDownloadsData->getPurchaseIdsExt();
        if(!is_array($purchasePostIds)){
          $purchasePostIds = (array)$purchasePostIds;
        }
        $systemDownloadIds = $this->easyDigitalDownloadsData->getIDS();
        $coreDownloadIds = $this->easyDigitalDownloadsData->getPurchaseIdsExt();
        $eddLogIds = $this->easyDigitalDownloadsData->getLogsIDS();
        if(!is_array($systemDownloadIds)){
          $systemDownloadIds = (array)$systemDownloadIds;
        }
        $allEddIds = array_merge($systemDownloadIds,$coreDownloadIds,$eddLogIds);
        // wp_die(print_r($allEddIds));
        // echo "Purchase POSTS: ".serialize($purchasePostIds);
        if( !empty($allEddIds) && count($allEddIds) > 0 ){
          foreach($this->postIDarray as $postIDKey => $postIDVal){
            if( in_array($postIDVal, $allEddIds) ){
              unset($this->postIDarray[$postIDKey]);
            }
          }
        }
      }
      // wp_die(print_r($this->postIDarray));
    }

    /**
    * Get the Posts Meta-Data for a given Post ID
    *
    * @return null|array
    */
    private function shgdprdm_getPostMeta($postID) {
      $postMeta = get_post_meta( $postID );
      if(empty($postMeta)){
        $postMeta = NULL;
      }
      return $postMeta;
    }

    /**
    * Update the Meta-Data for a given Post ID
    *
    * @return null
    */
    private function shgdprdm_updatePostMeta($postID, $postMetaArray, $replacementText){
      foreach($postMetaArray as $pindx => $pval){
        if(is_array($pval)){
          foreach($pval as $indpval){
            update_post_meta($postID, $pindx, $replacementText, $indpval);
          }
        }
        else{
          update_post_meta($postID, $pindx, $replacementText, $pval);
        }
      }
    }

    /**
    * Set the array of common values that will be used to update Posts
    *
    * @return null
    */
    private function shgdprdm_makeEmptyPostUpdateArray(){
      $this->shgdprdm_postUpdateArray = array(
       'ID' => '',
       // 'post_content' => $this->shgdprdm_option_user_deleted_text_content_post,
       // 'post_title' => $this->shgdprdm_option_user_deleted_text_title_post,
       'post_excerpt' => $this->shgdprdm_option_user_deleted_text_username,
       // 'comment_status' => 'closed',
       // 'post_name' => $this->shgdprdm_option_user_deleted_text_username,
     );
    }

    /**
    * Set the Post ID in the array of values that will be used to update a given Post ID
    *
    * @return null
    */
    private function shgdprdm_uniquePostUpdateArray($postID){
      $this->shgdprdm_postUpdateArray['ID'] = $postID;
    }

    /**
    * Getter: Get the arry of values for updating a Post
    *
    * @return array
    */
    private function shgdprdm_getPostUpdateArray(){
      return $this->shgdprdm_postUpdateArray;
    }

    /**
    * Delete All Posts & Post-Meta-Data
    * Itterate throught the array of Post ID for the User
    * Update the Meta-Data
    * Update the Posts
    *
    * @return boolean-update
    */
    public function shgdprdm_deletePostsAndMeta(){

      if(!empty($this->postIDarray) && count($this->postIDarray) > 0){
        foreach($this->postIDarray as $pid){
          $currentMeta = self::shgdprdm_getPostMeta($pid);
          if($currentMeta){
            self::shgdprdm_updatePostMeta( $pid, $currentMeta, $this->shgdprdm_option_user_deleted_metadata);
          }
          self::shgdprdm_uniquePostUpdateArray($pid);
          wp_update_post(self::shgdprdm_getPostUpdateArray());
        }
      }
      $this->postsDeleted = true;
    }





    // *************************
    // Delete Comments & Comments Meta
    // *************************


    /**
    * Get the ID's of all of this User's Comments
    *
    * @return null|setter
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
    * Get the Comments Meta-Data for a given Comment ID
    *
    * @return null|array
    *
    */
    private function shgdprdm_getCommentMeta($commentID) {
      $commentMeta = get_comment_meta( $commentID );
      if(empty($commentMeta)){
        $commentMeta = NULL;
      }
      return $commentMeta;
    }

    /**
    * Update the Meta-Data for a given Comment ID
    *
    * @return null
    */
    private function shgdprdm_updateCommentMeta($commentID, $commentMetaArray, $replacementText){
      foreach($commentMetaArray as $cindx => $cval){
        if(is_array($cval)){
          foreach($cval as $indcval){
            update_comment_meta($commentID, $cindx, $replacementText, $indcval);
          }
        }
        else{
          update_comment_meta($commentID, $cindx, $replacementText, $cval);
        }
      }
    }

    /**
    * Set the array of common values that will be used to update Comments
    *
    * @return null
    */
    private function shgdprdm_makeEmptyCommentUpdateArray(){
      $this->shgdprdm_commentUpdateArray = array(
       'comment_ID' => '',
       'comment_content' => $this->shgdprdm_option_user_deleted_text_content_comment,
       'comment_author' => $this->shgdprdm_option_user_deleted_text_username,
       'comment_author_email' => $this->shgdprdm_option_user_deleted_useremail,
       'comment_author_url' => $this->shgdprdm_option_user_deleted_text_username,
       'comment_agent' => $this->shgdprdm_option_user_deleted_text_username,
       'comment_author_IP' => $this->shgdprdm_option_user_deleted_text_username,
     );
    }

    /**
    * Set the Comment ID in the array of values that will be used to update a given Comment ID
    *
    * @return null
    */
    private function shgdprdm_uniqueCommentUpdateArray($commentID){
      $this->shgdprdm_commentUpdateArray['comment_ID'] = $commentID;
    }

    /**
    * Getter: Get the arry of values for updating a Comment
    *
    * @return array
    */
    private function shgdprdm_getCommentUpdateArray(){
      return $this->shgdprdm_commentUpdateArray;
    }

    /**
    * Delete All Posts & Post-Meta-Data
    * Itterate throught the array of Post ID for the User
    * Update the Meta-Data
    * Update the Posts
    *
    * @return boolean-update
    */
    public function shgdprdm_deleteCommentsAndMeta(){
      if( !empty($this->commentIDarray) && count($this->commentIDarray) > 0){
        foreach($this->commentIDarray as $cid){
          $currentMeta = self::shgdprdm_getCommentMeta($cid);
          if($currentMeta){
            self::shgdprdm_updateCommentMeta( $cid, $currentMeta, $this->shgdprdm_option_user_deleted_metadata);
          }
          self::shgdprdm_uniqueCommentUpdateArray($cid);
          wp_update_comment(self::shgdprdm_getCommentUpdateArray());
        }
      }
      $this->commentsDeleted = true;
    }


    // *************************
    // Delete WOO COMMERCE DATA
    // *************************

    /**
    * Get the Woo Commerce Order Object
    *
    * @return null|object
    */
    private function shgdprdm_getWooCommerceData(){
      if( $this->ueml ){
        if( class_exists('SHGdprdm_WCF') ){
          $this->wooCommerceData = new SHGdprdm_WCF($this->ueml);
        }
      }
    }

    private function shgdprdm_deleteWComPaymentTokens(){
      if( $this->uid ){
        if(class_exists('WC_Payment_Tokens')){
          $paymentTokens = WC_Payment_Tokens::get_customer_tokens( $this->uid );
          if(count($paymentTokens) > 0){
            $tokensMetaData = array();
            foreach($paymentTokens as $token){
                $token->delete();
            }
          }
        }
      }
    }

    private function shgdprdm_deleteWComOrders(){
      // Orders (Woocommerce)
      if( $this->ueml ){
        if( class_exists('SHGdprdm_WCF') ){
          $ordersD = new SHGdprdm_WCF($this->ueml);
          $hasOrders = $ordersD->hasOrders();
          if($hasOrders){
            $ordersD->runRequestUpdate();
          }
        }
      }
    }

    public function shgdprdm_deleteWooCommerce(){
      self::shgdprdm_deleteWComPaymentTokens();
      if( get_option('Woo-Commerce-Guest-Accounts') ){
        self::shgdprdm_deleteWComOrders();
      }
      $this->wooComDeleted = true;
    }


    // *************************
    // Delete Easy Digital Downloads DATA
    // *************************

    /**
    * Get the Easy Digital Downloads Purchases Object
    *
    * @return null|object
    */
    private function shgdprdm_getEasyDigitalDownloadsData(){
      if( $this->ueml ){
        if( class_exists('SHGdprdm_EDDF') ){
          $eddData = new SHGdprdm_EDDF($this->ueml);
          $this->eddPurchaseIds = $eddData->getIDS();
          $this->easyDigitalDownloadsData = $eddData;

        }
      }
    }

    // private function shgdprdm_deleteWComPaymentTokens(){
    //   if( $this->uid ){
    //     if(class_exists('WC_Payment_Tokens')){
    //       $paymentTokens = WC_Payment_Tokens::get_customer_tokens( $this->uid );
    //       if(count($paymentTokens) > 0){
    //         $tokensMetaData = array();
    //         foreach($paymentTokens as $token){
    //             $token->delete();
    //         }
    //       }
    //     }
    //   }
    // }

    private function shgdprdm_deleteEddDownloads(){
      // Purchases (Easy Digital Downloads)
      if( $this->ueml ){
        if( class_exists('SHGdprdm_EDDF') ){
          $customerDel = new SHGdprdm_EDDF($this->ueml);
          $hasDownloads = $customerDel->hasDownloads();
          if(!empty($customerDel)){
            $customerDel->runRequestUpdate();
          }
          // if($hasDownloads){
          //   $downloadsDel->runRequestUpdate();
          // }
        }
      }
    }

    public function shgdprdm_deleteEasyDigitalDownloads(){
      if( isset(get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin']) ){
        self::shgdprdm_deleteEddDownloads();
      }
      $this->easyDigiDlDeleted = true;
    }



    // *************************
    // Delete User & User Meta
    // *************************


    // https://wordpress.stackexchange.com/questions/45224/deleting-users-from-front-end-with-wp-delete-user
    // https://codex.wordpress.org/Plugin_API/Action_Reference/delete_user
    // https://developer.wordpress.org/reference/functions/wp_delete_user/
    // https://developer.wordpress.org/reference/hooks/delete_user/
    // https://www.google.nl/search?q=wordpress+developer+delete_user&oq=wordpress+developer+delete_user&aqs=chrome..69i57.8385j0j7&sourceid=chrome&ie=UTF-8


    /**
    * Delete All User & User-Meta-Data
    * But first re-assign all the Users posts & Comments to the GDM "Deleted User"
    * GDM "Deleted User" is a user created on plug-in activation
    * This can only be run once the following conditions are true:
    * -> Users Posts & Post-Meta have been Deleted/Updated
    * -> Users Comments & Comments-Meta have been Deleted/Udpated
    * -> The "GDM Deleted User" is confirmed as existing
    *
    * @return boolean-update
    */
    public function shgdprdm_deleteUser($isDisasterSync = FALSE){
      // Get the "GDM Deleted User" ID
      $reassignID = get_option('shgdprdm_deleted_user_id');
      // Check conditions are true befor eproceeding
      if($this->postsDeleted && $this->commentsDeleted && $this->wooComDeleted && ( $reassignID != null && $reassignID != '' && $reassignID !== '' && $reassignID != 0 && $reassignID != '0' ) ) {
        // $wp_delete_path =  dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ). '/wp-admin/includes/user.php';
        $wp_delete_path =  get_option('shgdprdm_user_fns'). 'wp-admin/includes/user.php';
        require_once($wp_delete_path);
        if($this->uid){
          wp_delete_user( $this->uid, $reassignID );
        }
        if($isDisasterSync === FALSE || $isDisasterSync == 0){
          self::shgdprdm_emailDeletedUser();
        }
        return true;
      }
      else{
        $adminEmail = get_bloginfo( "admin_email" );
        die("Error! User could not be deleted.<br>Please contact the site Administrator at <a href='mailto:".$adminEmail."'>".$adminEmail."</a>.");
      }
    }


  } // end of class
}

 ?>
