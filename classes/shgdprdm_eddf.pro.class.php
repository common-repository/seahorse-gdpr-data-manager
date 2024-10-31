<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
if (!class_exists('SHGdprdm_EDDF')) {
    class SHGdprdm_EDDF
    {
        public $customer;
        private $validSearchParam;
        protected $purchases;
        protected $uid;
        private $wpEdd;
        private $inclGuests;
        private $extCustomer;
        private $userSiteID;

        /** Class constructor */
        public function __construct()
        {
            $params= func_get_args();
            if (count($params) !== 1) {
                return false;
            }
            $this->uid = $params[0];


            // SH Note: Update to check is search param is set in options (not a requiremetn as all EDD are registered as WP Users)
            $this->inclGuests = true;


            // Check if EDD Customer

            // Passed Param must be Email or Int
            // If Email, then use function new EDD_Customer(email);
            // If Int, then use function new EDD_Customer(Int, true)

            // Check if WP Customer or EDD Guest
            // First Check if count customer greater then 0
            // then:
            // Check if customer->user_id
            // If true, then cross-check this value with WP_user function
            // If false, then EDD Guest

            // Cross-Check if EDD Guests are included in search

            // If WP Customer OR EDD Guest & Guests Included:
            // Then

            // Get Purchases Data

            // Passed Param must be Email or Int
            if (method_exists($this, 'valid_user_search')) {
                $this->valid_user_search();
            } else {
                //   echo "Failed on Fn Exists: Valid User Search (".__LINE__.")";
                return false;
            }

            // Get EDD Customer Details (provided that the search is valid)
            if ($this->validSearchParam) {
                if (method_exists($this, 'get_edd_customer')) {
                    $this->get_edd_customer();
                } else {
                    // echo "Failed on Fn Exists: Get Edd Customer (".__LINE__.")";
                    return false;
                }
            } else {
                //   echo "Failed: Invalid User Search (".__LINE__.")";
                return false;
            }

            // Check if the customre is a WP user or EDD Guest (provided there is a valid customer)
            if ($this->customerCount) {
                if (method_exists($this, 'is_edd_wp_guest')) {
                    $this->is_edd_wp_guest();
                } else {
                    // echo "Failed on Fn Exists: Is Edd WP User or Guest (".__LINE__.")";
                    return false;
                }
            } else {
                // echo "Failed: No Customer Exists (".__LINE__.")";
                return false;
            }

            // Get the Payment Details (If EDD customer is WP User or EDD Customer is Guest AND search-by guests is selected)
            if ($this->wpEdd || (!$this->wpEdd && $this->inclGuests)) {
                if (method_exists($this, 'get_edd_purchases')) {
                    $this->get_edd_purchases();
                } else {
                    // echo "Failed on Fn Exists: Get EDD Purchases (".__LINE__.")";
                    return false;
                }
            }
            //   else{
    //       echo "Failed: Request Outside of Search Conditions (".__LINE__.")";
    //   }

    //   echo "End of EDD Consructor";
        } // end constructor

        // ******* Public Functions ********

        // Return the Search parameter (testing)
        public function edd_returnParam()
        {
            return $this->uid;
        }
        // Return the Customer Data
        public function edd_returnCust()
        {
            if ($this->customerCount) {
                return $this->customer;
            }
            return false;
        }
        // Return the customers Purchase Data
        public function edd_returnPurch()
        {
            return $this->purchases;
        }



        // ******* Private Functions ********
        /**
   * Check if Search Param is Valid
   * ust be email or int
   *
   * @return boolean
   */
        private function valid_user_search()
        {
            //   echo "In Fn: valid_user_search() | ".__LINE__;
            $this->validSearchParam = false;
            $this->functionID = false;
            if (is_email($this->uid)) {
                $this->validSearchParam = true;
                return true;
            }
            if (is_numeric($this->uid)) {
                $this->validSearchParam = true;
                $this->functionID = true;
                return true;
            }
            return false;
        }


        /**
   * Check if Search Param is Valid
   * ust be email or int
   *
   * @return boolean
   */
        private function get_edd_customer()
        {
            //   echo "In Fn: get_edd_customer() | ".__LINE__;
            // Initialise customer count to nill
            $this->customerCount = false;

            if (class_exists('EDD_Customer')) {
                // If Search Param is Int then apply the WP User ID function
                if ($this->functionID) {
                    //   echo "<br>Looking for EDD by INT<br>";
                    $this->customer = new EDD_Customer($this->uid, true);
                }
                // Otherwise search by email
                else {
                    //   echo "<br>Looking for EDD by Email<br>";
                    $this->customer = new EDD_Customer($this->uid);
                    //   echo "<br>EDD CUSTOMER:<br>";
            //   print_r($this->customer);
            //     echo "<br><br>";
                }
            }
            //   echo "<br>IS CUSTOMER: ".!empty( $this->customer );
            //   echo "<br>Count Customer: ".count( (array)$this->customer );
            //   echo "<br>Customer ID: ".$this->customer->id;
            // Check if there is returned customer info
            if (!empty($this->customer) && $this->customer->id) {
                $this->customerCount = true;
                //   echo "<br>Customer Count: TRUE";
                return true;
            }
            //   echo "<br>Customer Count: FALSE";
            return false;
        }


        /**
        * Get type of User (EDD Guest or WP User)
        *
        * @return null|string
        */
        private function is_edd_wp_guest()
        {
            // echo "In Fn: is_edd_wp_guest() | ".__LINE__;
            if (!$this->customer->user_id) {
                $this->wpEdd = false;
            } else {
                // Check if WP user AND that the email addy is same as EDD customer
                $wpUser = get_user_by('id', $this->customer->user_id);
                if (!empty($wpUser)) {
                    if ($wpUser->user_email == $this->customer->email) {
                        $this->userSiteID = $this->customer->user_id;
                        $this->wpEdd = true;
                        return true;
                    } else {
                        $this->wpEdd = false;
                    }
                } else {
                    $this->wpEdd = false;
                }
            }
            return false;
        }

        /**
         * Get type of User
         *
         * @return null|string
         */
        private function get_user_search()
        {
            //   echo "In Fn: get_user_search() | ".__LINE__;
            if (is_email($this->uid)) {
                return 'email';
            } elseif (is_numeric($this->uid)) {
                return 'cid';
            } else {
                return false;
            }
        }


        /**
   * Retrieve purchases data for customer
   *
   * @return object
   */
        private function get_edd_purchases()
        {
            //   echo "In Fn: get_edd_purchases() | ".__LINE__;
            $this->purchases = $this->customer->get_payments();
            return true;
        }




        /**
 * Returns the count of records of orders
 *
 * @return null|string
 */
        private function record_count()
        {
            if (!empty($this->purchases)) {
                return count((array)$this->purchases);
            }
            return 0;
        }

        /**
         * Render a view of Data
         *
         * @return mixed
         */
        public function hasDownloads()
        {
            return ($this->purchases && $this->record_count() && $this->record_count() > 0) ? true : false;
        }

        /**
         * Get the ID of an Order
         *
         * @return mixed
         */
        public function getIDS()
        {
            return $this->customer->payment_ids;
        }

        public function getPurchaseIdsExt()
        {
            $siteID = $this->customer->user_id ? $this->customer->user_id : false;
            $purchasesIDs = array();
            if ($siteID) {
                // wp_die('Site ID: '.$siteID);
                // wp_reset_postdata();
                // wp_reset_query();
                // global $post;
                $eddPurchases = new WP_Query(array( 'author' => $siteID, 'post_type' => 'edd_payment' ));
                $eddPurchases = $eddPurchases->posts;
                if (!empty($eddPurchases)) {
                    // wp_die(print_r($siteID));
                    foreach ($eddPurchases as $purchase) {
                        array_push($purchasesIDs, $purchase->ID);
                    }
                }
            }
            return $purchasesIDs;
        }


        /**
        * Get the ID of an Logs
        *
        * @return mixed
        */
        public function getLogsIDS()
        {
            $siteID = $this->customer->user_id ? $this->customer->user_id : false;
            $logIDs = array();
            if ($siteID) {
                // wp_die('Site ID: '.$siteID);
                // wp_reset_postdata();
                // wp_reset_query();
                // global $post;
                $eddLogs = new WP_Query(array( 'author' => $siteID, 'post_type' => 'edd_log' ));
                $eddLogs = $eddLogs->posts;
                if (!empty($eddLogs)) {
                    // wp_die(print_r($siteID));
                    foreach ($eddLogs as $log) {
                        array_push($logIDs, $log->ID);
                    }
                }
            }
            return $logIDs;
        }




        /** Text displayed when no post data is available */
        public function no_items()
        {
            _e('No records avaliable.', 'sp');
        }

        /**
         * Render a view of Data
         *
         * @return mixed
         */
        //   public function display_orders(){
//      $display = '';
//      $display .= "<br>Data Records (Orders): ".self::record_count()." ";
//      if(!$this->orders){
//       return $display;
//      }
//      $display = '';
//      $display .= "<br>Data Records (Orders): ".self::record_count()." ";
//      $recordCount = 0;
//      foreach($this->orders as $order){
//       $display.= "<br><strong>Record ".$recordCount."</strong>:{";

//       $display.= "<br>&nbsp;<strong>Customer Details</strong>:{";
//       $display.= "Site User ID:". ($order->get_user() ? $order->get_user()->ID : "guest").", "; // false for guests
//       $display.= "Customer ID:".$order->get_customer_id().", ";
//       $display.= "}, ";

//       $display.= "<br>&nbsp;<strong>Order Details</strong>:{";
//       $display.= "Order ID:".$order->get_id().", ";
//       $display.= "Order Status:".$order->get_status().", ";
//       $display.= "Order Total:".$order->get_formatted_order_total().", ";
//       $display.= $order->get_created_via() ? "Order Generated By:".$order->get_created_via().", " : "";
//       $display.= $order->get_customer_ip_address() ? "Order Origin (IP):".$order->get_customer_ip_address().", " : "";
//       $display.= $order->get_customer_user_agent() ? "Order Origin (Agent):".$order->get_customer_user_agent().", " : "";
//       $display.= $order->get_customer_note() ? "Order Notes:".$order->get_customer_note().", " : "";
//       $display.= "Order Completed On:". ($order->get_date_completed() ? $order->get_date_completed() : "pending") .", ";
//       $display.= $order->get_cart_hash() ? "Order Cart:".$order->get_cart_hash() : "";
//       $display.= "}, ";

//       $display.= "<br>&nbsp;<strong>Payment Details</strong>:{";
//       $display.= $order->get_payment_method() ? "Payment Method:".$order->get_payment_method().", ": "";
//       $display.= $order->get_payment_method_title() ? "Payment Method Name:".$order->get_payment_method_title().", ": "";
//       $display.= $order->get_transaction_id() ? "Payment Reference:".$order->get_transaction_id().", ": "";
//       $display.= $order->get_date_paid() ? "<br>Order Paid On:".$order->get_date_paid().", ": "";
//       $display.= "}, ";

//       $display.= "<br>&nbsp;<strong>Billing Details</strong>:{";
//       $display.= $order->get_formatted_billing_full_name() ? "Billing Name:".$order->get_formatted_billing_full_name().", ": "";
//       if($order->has_billing_address()){
//         $display.= "Billing Address:".self::makeDisplayAddress($order, 'billing').", ";
//       }
//       $display.= $order->get_billing_email() ? "Order Billing Email:".$order->get_billing_email().", ": "";
//       $display.= $order->get_billing_phone() ? "Order Billing Phone:".$order->get_billing_phone(): "";
//       $display.= "}, ";

//       $display.= "<br>&nbsp;<strong>Shipping Details</strong>:{";
//       $display.= $order->get_formatted_shipping_full_name() ? "Shipping Name:".$order->get_formatted_shipping_full_name().", ": "";
//       if($order->has_shipping_address()){
//         $display.= "Shipping Address:".self::makeDisplayAddress($order, 'shipping').", ";
//       }
//       $display.= "Shipping Maps:".$order->get_shipping_address_map_url();
//       $display.= "}";

//       $display.= "<br>}";
//      }
//      $display.= "<br>";
//      return $display;
        //   }

        /**
         * Create array of Data
         *
         * @return array
         */
        public function shgdprdm_eddAddPurchases()
        {
            $recordCount = 0;
            $data = array();
            $records = array("Data Records (Orders): ".self::record_count());

            if (!empty($this->customer) && $this->customerCount) {
                $dataRecord = (object)array(
            'Customer Details' => self::getCustomerDetails(),
        );
                array_push($data, $dataRecord);
            }
            $purchases = $this->edd_returnPurch();
            if ($purchases) {
                foreach ($purchases as $ref => $purchaseDetail) {
                    $dataRecord = (object)array(
            // 'Customer Details' => self::getCustomerDetails($purchaseDetail),
            'Purchase Details' => self::getPurchaseDetails($purchaseDetail),
            // 'Payment Details' => self::getPaymentDetails($order),
            // 'Billing Details' => self::getBillingDetails($order),
            // 'Shipping Details' => self::getShippingDetails($order)
          );
                    array_push($data, $dataRecord);
                }
            }

            return $data;
        }

        public function getSiteID()
        {
            return ($this->userSiteID ? $this->userSiteID : "Guest");
        }

        public function getEmail()
        {
            return $this->userEmail;
        }


        private function makeDisplayAddress($orderDetails, $addressType)
        {
            $addressFields = array('company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country');

            $address = '[';
            $country = call_user_func_array(array($orderDetails, "get_".$addressType.'_country'), array());
            foreach ($addressFields as $field) {
                $fieldValue = call_user_func_array(array($orderDetails, "get_".$addressType.'_'.$field), array());
                if ($fieldValue) {
                    if ($field == 'state') {
                        $fieldValue = WC()->countries->states[$country][$fieldValue];
                    }
                    if ($field == 'country') {
                        $fieldValue = WC()->countries->countries[$fieldValue];
                    }
                    $address .= $fieldValue.', ';
                }
            }
            $address = rtrim($address, ', ');
            $address .= ']';
            return $address;
        }

        private function getCustomerDetails()
        {
            $customerDetails = array(
       "Site User ID" => $this->customer->user_id,
       "Customer ID" => $this->customer->id,
       "Customer Name" => $this->customer->name,
       "Customer Email" => $this->customer->email,
       "Alternative Emails" => json_encode($this->customer->emails),
       "No. Purchases" => $this->customer->purchase_count,
       "Purchase References" => json_encode($this->customer->payment_ids),
       "Total Purchases Value" => $this->customer->purchase_value,
       "Customer Created" => $this->customer->date_created
       );
            return $customerDetails;
        }

        // Purchase Details
        private function getPurchaseDetailKeys()
        {
            $purchaseDetailKeys = array(
       "Purchase ID",
       "Purchase Date",
       "Purchase Email",
       "Purchase Address",
       "Purchase Status",
       "Purchase Total",
       "Currency",
       "Subtotal",
       "Tax",
       "Fees",
       "Discounts",
       "Purchase Mode",
       "Licence Key",
       "Purchase Origin (IP)",
       "Payment Gateway",
       "Purchase Completed On",
       "Purchase Cart"
     );
            return $purchaseDetailKeys;
        }

        private function getPurchaseDetailValues($purchase)
        {
            $purchaseDetailVals = array(
           $purchase->ID,
           $purchase->date,
           $purchase->email,
           json_encode($purchase->address),
           $purchase->status_nicename,
           $purchase->total,
           $purchase->currency,
           $purchase->subtotal,
           $purchase->tax,
           $purchase->fees,
           $purchase->discounts,
           $purchase->mode,
           $purchase->key,
           $purchase->ip,
           $purchase->gateway,
           $purchase->completed_date,
           json_encode($purchase->cart_details)
        );
            return $purchaseDetailVals;
        }

        private function getPurchaseDetails($purchase)
        {
            $purchaseDetailKeys = self::getPurchaseDetailKeys();
            $purchaseDetailValues = self::getPurchaseDetailValues($purchase);
            return $purchaseDetails = self::makeDetailsArray($purchaseDetailKeys, $purchaseDetailValues);
        }

        //   // Payment Details
        //   private function getPaymentDetailKeys(){
//      $paymentDetailKeys = array(
//       "Payment Method","Payment Method Name","Payment Reference","Order Paid On"
//      );
//      return $paymentDetailKeys;
        //   }

        //   private function getPaymentDetailValues($order){

//      $opm = $order->get_payment_method() ? $order->get_payment_method() : 'No Record';
//      $opmn = $order->get_payment_method_title() ? $order->get_payment_method_title() : 'No Record';
//      $opr = $order->get_transaction_id() ? $order->get_transaction_id() : 'No Record';
//      $opo = $order->get_date_paid() ? $order->get_date_paid() : 'No Record';

//      $paymentDetailVals = array($opm,$opmn,$opr,$opo);
//      return $paymentDetailVals;
        //   }
//
        //   private function getPaymentDetails($order){
//      $paymentDetailKeys = self::getPaymentDetailKeys();
//      $paymentDetailValues = self::getPaymentDetailValues($order);
//      return $paymentDetails = self::makeDetailsArray($paymentDetailKeys,$paymentDetailValues);
        //   }

        //   // Billing Details
        //   private function getBillingDetailKeys(){
//      $billingDetailKeys = array(
//       "Billing Name","Billing Address","Order Billing Email","Order Billing Phone"
//      );
//      return $billingDetailKeys;
        //   }

        //   private function getBillingDetailValues($order){
//      $obn = $order->get_formatted_billing_full_name() ? $order->get_formatted_billing_full_name() : 'No Record';
//      $oba = $order->has_billing_address() ? self::makeDisplayAddress($order, 'billing') : 'No Record';
//      $obe = $order->get_billing_email() ? $order->get_billing_email() : 'No Record';
//      $obp = $order->get_billing_phone() ? $order->get_billing_phone() : 'No Record';

//      $billingDetailVals = array($obn,$oba,$obe,$obp);
//      return $billingDetailVals;
        //   }

        //   private function getBillingDetails($order){
//      $billingDetailKeys = self::getBillingDetailKeys();
//      $billingDetailValues = self::getBillingDetailValues($order);
//      return $billingDetails = self::makeDetailsArray($billingDetailKeys,$billingDetailValues);
        //   }

        //   // Shipping Details
        //   private function getShippingDetailKeys(){
//      $shippingDetailKeys = array(
//       "Shipping Name","Shipping Address","Shipping Maps"
//      );
//      return $shippingDetailKeys;
        //   }

        //   private function getShippingDetailValues($order){
//      $osn = $order->get_formatted_shipping_full_name() ? $order->get_formatted_shipping_full_name() : 'No Record';
//      $osa = $order->has_shipping_address() ? self::makeDisplayAddress($order, 'shipping') : 'No Record';
//      $osm = $order->get_shipping_address_map_url() ? $order->get_shipping_address_map_url() : 'No Record';

//      $shippingDetailVals = array($osn,$osa,$osm);
//      return $shippingDetailVals;
        //   }

        //   private function getShippingDetails($order){
//      $shippingDetailKeys = self::getShippingDetailKeys();
//      $shippingDetailValues = self::getShippingDetailValues($order);
//      return $shippingDetails = self::makeDetailsArray($shippingDetailKeys,$shippingDetailValues);
        //   }

        // Common
        private function makeDetailsArray($keys, $values)
        {
            $details = array();

            $detailsLen = count($values);
            for ($i = 0; $i < $detailsLen; $i++) {
                if ($values[$i]) {
                    $details[$keys[$i]] = $values[$i];
                }
            }
            return $details;
        }

        private function deleteDownloadDetails()
        {
            $rText = get_option('shgdprdm_text_options')['text_option'];
            $rEmail = (isset(get_option('shgdprdm_text_options')['email_option']) && get_option('shgdprdm_text_options')['email_option'] != '') ? get_option('shgdprdm_text_options')['email_option'] : 'deleted@deleted.com';

            $now  = new DateTime();
            $now = $now->format('Y-m-d-H-i-s');
            $replacEmail = 'deleted@'.$now.'.com';
            if (class_exists('EDD_Customer')) {
                if (!empty(new EDD_Customer($replacEmail) )) {
                    $now  = new DateTime();
                    $now = $now->format('Y-m-d-H-i-s');
                }
            }
            



            if (!empty($this->purchases)) {
                foreach ($this->purchases as $purchase) {
                    $purchase->first_name = $rText;
                    $purchase->last_name = $rText;
                    $purchase->address = array('line1' =>$rText, 'line2' =>$rText );
                    $purchase->ip = $rText;
                    $purchase->notes = $rText;
                    $purchase->save();
                }
            }

            // Delete Customer


            $updateArgs = array(
         'name' => $rText,
         'email' => $replacEmail,
        //  'notes' => $rText,
         'address' => array('line1' =>$rText, 'line2' =>$rText )
         );


            $this->customer->update($updateArgs);

            //  wp_die("End Delete Downloads Function");
        }


        public function runRequestUpdate()
        {
            if (!get_option('shgdprdm_text_options')['text_option']) { // Confirm that there is an overwite Text
                return false;
            }
            if (is_numeric($this->userSiteID)) {
                if (user_can($this->userSiteID, 'administrator')) {
                    return false;
                } elseif (user_can($this->userSiteID, 'manage_options')) {
                    return false;
                }
            }
            self::deleteDownloadDetails();
        }

        public function getTokenInfo()
        {
            $tokens = array();
            foreach ($this->orders as $order) {
                array_push($tokens, $order->get_token);
            }
            return $tokens;
        }

        public function getAdvancedPosts($args_array)
        {
            if (!is_array($args_array)) {
                $searchCondition = 'post_author';
                $searchParam = $args_array;
            } elseif (array_key_exists('author', $args_array)) {
                $searchCondition = 'post_author';
                $searchParam = $args_array['author'];
            }
            // Could be expanded to search for other condtions eg date, title etc
            else {
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
