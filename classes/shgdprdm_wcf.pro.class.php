<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
if(!class_exists('SHGdprdm_WCF')){
class SHGdprdm_WCF {

  protected $orders;
  protected $uid;
  protected $userSiteID;
  protected $userEmail;

	/** Class constructor */
	public function __construct() {

      if(count(func_get_args()) !== 1){
        return FALSE;
      }
      $this->uid = func_get_args()[0];

      if ( method_exists($this, 'get_user_search' ) && method_exists($this, 'get_orders' ) ) {
        $this->orders = self::get_orders();
        if(count($this->orders) > 0 && !empty($this->orders[0])){
          if(isset($this->orders[0]->get_user()->ID)) {
          $this->userSiteID = $this->orders[0]->get_user()->ID ? $this->orders[0]->get_user()->ID : 'Guest';
           }
          if($this->userSiteID != 'Guest'){
            if(isset($this->orders[0]->get_user()->Email)) {
            $this->userEmail = $this->orders[0]->get_user()->Email;
            }
          }
          else{
            $this->userEmail = $this->orders[0]->get_billing_email();
          }
        }
      }
      else{
        $this->orders = FALSE;
      }

	}


  /**
   * Get type of User
   *
   * @return null|string
   */
  private function get_user_type() {
    if(is_email($this->uid) ){
      return 'guest';
    }
    else if(is_numeric($this->uid)){
      return 'user';
    }
    else{
      return FALSE;
    }
  }

    /**
     * Get type of User
     *
     * @return null|string
     */
    private function get_user_search() {
      if(is_email($this->uid) ){
        return 'billing_email';
      }
      else if(is_numeric($this->uid)){
        return 'customer_id';
      }
      else{
        return FALSE;
      }
    }

    /**
   * Retrieve order data from the database
   *
   * @return object
   */
  private function get_orders() {

    $searchBy = self::get_user_search();

    if(!$searchBy){
      return FALSE;
    }
    if(isset(get_option('Woo-Commerce-Guest-Accounts')['Woo-Commerce-Guest-Accounts']) && function_exists('wc_get_orders') ){
      $orderObj = wc_get_orders(
        array($searchBy => $this->uid)
      );
    }
    else{
      if(is_email($this->uid) ){
        $user = get_user_by( 'email', $this->uid );
      }
      else if(is_numeric($this->uid)){
        $user = get_user_by( 'id', $this->uid );
      }
      else{
        $user = FALSE;
      }
      if($user){
        if( function_exists('wc_get_orders') ){
          $orderObj = wc_get_orders(
            array('customer_id' => $user->ID)
          );
        }
        else{
          $orderObj = array();
        }
      }
      else{
        // echo "<br>FALSE User ID: ".$this->uid;
        return FALSE;
      }
    }


    return $orderObj;
  }

  /**
 * Returns the count of records of orders
 *
 * @return null|string
 */
  private function record_count() {
    return count($this->orders);
  }

  /**
   * Render a view of Data
   *
   * @return mixed
   */
   public function hasOrders() {
     return ($this->orders && self::record_count() && self::record_count() > 0 ) ? true : false;
   }

   /**
    * Get the ID of an Order
    *
    * @return mixed
    */
    public function getIDS() {
      $orderIds = array();
      if($this->orders){
        foreach($this->orders as $order){
          if($order->get_id()){
            array_push($orderIds, $order->get_id());
          }
        }
      }
      return $orderIds;
    }

  /** Text displayed when no post data is available */
  public function no_items() {
    _e( 'No records avaliable.', 'sp' );
  }

  /**
   * Render a view of Data
   *
   * @return mixed
   */
   public function display_orders(){
     $display = '';
     $display .= "<br>Data Records (Orders): ".self::record_count()." ";
     if(!$this->orders){
       return $display;
     }
     $display = '';
     $display .= "<br>Data Records (Orders): ".self::record_count()." ";
     $recordCount = 0;
     foreach($this->orders as $order){
      $display.= "<br><strong>Record ".$recordCount."</strong>:{";

      $display.= "<br>&nbsp;<strong>Customer Details</strong>:{";
      $display.= "Site User ID:". ($order->get_user() ? $order->get_user()->ID : "guest").", "; // false for guests
      $display.= "Customer ID:".$order->get_customer_id().", ";
      $display.= "}, ";

      $display.= "<br>&nbsp;<strong>Order Details</strong>:{";
      $display.= "Order ID:".$order->get_id().", ";
      $display.= "Order Status:".$order->get_status().", ";
      $display.= "Order Total:".$order->get_formatted_order_total().", ";
      $display.= $order->get_created_via() ? "Order Generated By:".$order->get_created_via().", " : "";
      $display.= $order->get_customer_ip_address() ? "Order Origin (IP):".$order->get_customer_ip_address().", " : "";
      $display.= $order->get_customer_user_agent() ? "Order Origin (Agent):".$order->get_customer_user_agent().", " : "";
      $display.= $order->get_customer_note() ? "Order Notes:".$order->get_customer_note().", " : "";
      $display.= "Order Completed On:". ($order->get_date_completed() ? $order->get_date_completed() : "pending") .", ";
      $display.= $order->get_cart_hash() ? "Order Cart:".$order->get_cart_hash() : "";
      $display.= "}, ";

      $display.= "<br>&nbsp;<strong>Payment Details</strong>:{";
      $display.= $order->get_payment_method() ? "Payment Method:".$order->get_payment_method().", ": "";
      $display.= $order->get_payment_method_title() ? "Payment Method Name:".$order->get_payment_method_title().", ": "";
      $display.= $order->get_transaction_id() ? "Payment Reference:".$order->get_transaction_id().", ": "";
      $display.= $order->get_date_paid() ? "<br>Order Paid On:".$order->get_date_paid().", ": "";
      $display.= "}, ";

      $display.= "<br>&nbsp;<strong>Billing Details</strong>:{";
      $display.= $order->get_formatted_billing_full_name() ? "Billing Name:".$order->get_formatted_billing_full_name().", ": "";
      if($order->has_billing_address()){
        $display.= "Billing Address:".self::makeDisplayAddress($order, 'billing').", ";
      }
      $display.= $order->get_billing_email() ? "Order Billing Email:".$order->get_billing_email().", ": "";
      $display.= $order->get_billing_phone() ? "Order Billing Phone:".$order->get_billing_phone(): "";
      $display.= "}, ";

      $display.= "<br>&nbsp;<strong>Shipping Details</strong>:{";
      $display.= $order->get_formatted_shipping_full_name() ? "Shipping Name:".$order->get_formatted_shipping_full_name().", ": "";
      if($order->has_shipping_address()){
        $display.= "Shipping Address:".self::makeDisplayAddress($order, 'shipping').", ";
      }
      $display.= "Shipping Maps:".$order->get_shipping_address_map_url();
      $display.= "}";

      $display.= "<br>}";
     }
     $display.= "<br>";
     return $display;
   }

   /**
    * Create array of Data
    *
    * @return array
    */
    public function shgdprdm_wcfAddOrders(){
      $recordCount = 0;
      $data = array();
      $records = array("Data Records (Orders): ".self::record_count());


      if($this->orders){
        foreach($this->orders as $order){

          $dataRecord = (object)array(
            'Customer Details' => self::getCustomerDetails($order),
            'Order Details' => self::getOrderDetails($order),
            'Payment Details' => self::getPaymentDetails($order),
            'Billing Details' => self::getBillingDetails($order),
            'Shipping Details' => self::getShippingDetails($order)
          );
          array_push($data,$dataRecord);
        }
      }

      return $data;
    }

    public function getSiteID(){
      return ($this->userSiteID ? $this->userSiteID : "Guest");
    }

    public function getEmail(){
      return $this->userEmail;
    }


   private function makeDisplayAddress($orderDetails, $addressType){
     $addressFields = array('company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');

     $address = '[';
     $country = call_user_func_array( array($orderDetails, "get_".$addressType.'_country'), array() );
     foreach($addressFields as $field){
      $fieldValue = call_user_func_array( array($orderDetails, "get_".$addressType.'_'.$field), array() );
      if($fieldValue){
        if($field == 'state'){
          $fieldValue = WC()->countries->states[$country][$fieldValue];
        }
        if ($field == 'country'){
          $fieldValue = WC()->countries->countries[$fieldValue];
        }
        $address .= $fieldValue.', ';
      }
     }
     $address = rtrim($address, ', ');
     $address .= ']';
     return $address;
   }

   private function getCustomerDetails($order){
     $sid = ($order->get_user() ? $order->get_user()->ID : "guest"); // false for guests
     $customerDetails = array(
       "Site User ID" => $sid,
       "Customer ID" => $order->get_customer_id()
     );
     return $customerDetails;
   }

   // Order Details
   private function getOrderDetailKeys(){
     $orderDetailKeys = array(
       "Order_ID","Order Status","Order Total",
       "Order_Generated_By","Order Origin (IP)","Order Origin (Agent)",
       "Order Notes","Order Completed On","Order Cart"
     );
     return $orderDetailKeys;
   }

   private function getOrderDetailValues($order){
     $ogb = $order->get_created_via() ? $order->get_created_via() : 'No Record';
     $ooip = $order->get_customer_ip_address() ? $order->get_customer_ip_address() : 'No Record';
     $ooa = $order->get_customer_user_agent() ? $order->get_customer_user_agent() : 'No Record';
     $onot = $order->get_customer_note() ? $order->get_customer_note() : 'No Record';
     $ocom = $order->get_date_completed() ? $order->get_date_completed() : "pending";
     $ocrt = $order->get_cart_hash() ? $order->get_cart_hash() : 'No Record';

     $orderDetailVals = array(
       $order->get_id(),
       $order->get_status(),
       $order->get_formatted_order_total(),
       $ogb,$ooip,$ooa,$onot,$ocom, $ocrt
     );
     return $orderDetailVals;
   }

   private function getOrderDetails($order){
     $orderDetailKeys = self::getOrderDetailKeys();
     $orderDetailValues = self::getOrderDetailValues($order);
     return $orderDetails = self::makeDetailsArray($orderDetailKeys,$orderDetailValues);
   }

   // Payment Details
   private function getPaymentDetailKeys(){
     $paymentDetailKeys = array(
       "Payment Method","Payment Method Name","Payment Reference","Order Paid On"
     );
     return $paymentDetailKeys;
   }

   private function getPaymentDetailValues($order){

     $opm = $order->get_payment_method() ? $order->get_payment_method() : 'No Record';
     $opmn = $order->get_payment_method_title() ? $order->get_payment_method_title() : 'No Record';
     $opr = $order->get_transaction_id() ? $order->get_transaction_id() : 'No Record';
     $opo = $order->get_date_paid() ? $order->get_date_paid() : 'No Record';

     $paymentDetailVals = array($opm,$opmn,$opr,$opo);
     return $paymentDetailVals;
   }

   private function getPaymentDetails($order){
     $paymentDetailKeys = self::getPaymentDetailKeys();
     $paymentDetailValues = self::getPaymentDetailValues($order);
     return $paymentDetails = self::makeDetailsArray($paymentDetailKeys,$paymentDetailValues);
   }

   // Billing Details
   private function getBillingDetailKeys(){
     $billingDetailKeys = array(
       "Billing Name","Billing Address","Order Billing Email","Order Billing Phone"
     );
     return $billingDetailKeys;
   }

   private function getBillingDetailValues($order){
     $obn = $order->get_formatted_billing_full_name() ? $order->get_formatted_billing_full_name() : 'No Record';
     $oba = $order->has_billing_address() ? self::makeDisplayAddress($order, 'billing') : 'No Record';
     $obe = $order->get_billing_email() ? $order->get_billing_email() : 'No Record';
     $obp = $order->get_billing_phone() ? $order->get_billing_phone() : 'No Record';

     $billingDetailVals = array($obn,$oba,$obe,$obp);
     return $billingDetailVals;
   }

   private function getBillingDetails($order){
     $billingDetailKeys = self::getBillingDetailKeys();
     $billingDetailValues = self::getBillingDetailValues($order);
     return $billingDetails = self::makeDetailsArray($billingDetailKeys,$billingDetailValues);
   }

   // Shipping Details
   private function getShippingDetailKeys(){
     $shippingDetailKeys = array(
       "Shipping Name","Shipping Address","Shipping Maps"
     );
     return $shippingDetailKeys;
   }

   private function getShippingDetailValues($order){
     $osn = $order->get_formatted_shipping_full_name() ? $order->get_formatted_shipping_full_name() : 'No Record';
     $osa = $order->has_shipping_address() ? self::makeDisplayAddress($order, 'shipping') : 'No Record';
     $osm = $order->get_shipping_address_map_url() ? $order->get_shipping_address_map_url() : 'No Record';

     $shippingDetailVals = array($osn,$osa,$osm);
     return $shippingDetailVals;
   }

   private function getShippingDetails($order){
     $shippingDetailKeys = self::getShippingDetailKeys();
     $shippingDetailValues = self::getShippingDetailValues($order);
     return $shippingDetails = self::makeDetailsArray($shippingDetailKeys,$shippingDetailValues);
   }

   // Common
   private function makeDetailsArray($keys,$values){
     $details = array();

     $detailsLen = count($values);
     for($i = 0; $i < $detailsLen; $i++){
       if($values[$i]){
         $details[$keys[$i]] = $values[$i];
       }
     }
     return $details;
   }

   private function deleteOrderDetails(){
     $rText = get_option('shgdprdm_text_options')['text_option'];
     $rEmail = (isset(get_option('shgdprdm_text_options')['email_option']) && get_option('shgdprdm_text_options')['email_option'] != '') ? get_option('shgdprdm_text_options')['email_option'] : 'deleted@deleted.com';
     foreach($this->orders as $order){
       self::shgdprdm_wc_set_shipping_phone($order->get_id(), '00000000' ); // There is no native function for this
       $order->set_billing_first_name( $rText );
       $order->set_billing_last_name( $rText );
       $order->set_billing_company( $rText );
       $order->set_billing_address_1( $rText );
       $order->set_billing_address_2( $rText );
       $order->set_billing_postcode( $rText );
       $order->set_billing_email( $rEmail );
       $order->set_billing_phone( '00000000' );
       $order->set_shipping_first_name( $rText );
       $order->set_shipping_last_name( $rText );
       $order->set_shipping_company( $rText );
       $order->set_shipping_address_1( $rText );
       $order->set_shipping_address_2( $rText );
       $order->set_shipping_postcode( $rText );
       $order->set_payment_method( $rText );
       $order->set_payment_method_title( $rText );
       $order->set_customer_ip_address( $rText );
       $order->set_customer_user_agent( $rText );
       $order->set_created_via( $rText );
       $order->set_customer_note( $rText );
       $order->set_date_completed( $rText );

       $order->set_customer_note( $rText );
       $order->save();
       self::shgdprdm_wc_set_shipping_phone($order->get_id(), '00000000' ); // There is no native function for this

     }
   }

   public function runRequestUpdate(){
     if(!get_option('shgdprdm_text_options')['text_option']){ // Confirm that there is an overwite Text
       return false;
     }
     if( is_numeric( $this->userSiteID ) ) {
       if( user_can( $this->userSiteID , 'administrator' ) ) {
         return false;
       }
       else if( user_can( $this->userSiteID , 'manage_options' ) ){
         return false;
       }
     }
     self::deleteOrderDetails();
   }

   private function shgdprdm_wc_set_shipping_phone($orderID, $replacementVal){
    if(!$replacementVal || $replacementVal == ''){
      return false;
    }
    $replacementVal = sanitize_text_field($replacementVal);
    if( shgdprdm_validatePhone($replacementVal) ){
      update_post_meta( $orderID, '_shipping_phone', $replacementVal );
      return true;
    }
    return false;
   }

   public function getTokenInfo(){
     $tokens = array();
     foreach($this->orders as $order){
       array_push($tokens,$order->get_token);
     }
     return $tokens;
   }

} // end of class
}

 ?>