<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

if(!class_exists('SHGdprdm_SYNCRECORD')){
class SHGdprdm_SYNCRECORD {

	private $externalDataRecordRef;
	private $externalDataRecords;
	private $validRecord;
	private $localUpdatedRef;
	private $adminReExport;
	private $adminReExportEmail;
	private $updateExtData;

	/** Class constructor */
	public function __construct() {

		if(count(func_get_args()) !== 1){
      throw new Exception('Error! Action cannot be performed.');
    }
    $this->adminReExport = FALSE;
		$this->adminReExportEmail = 51;

		$this->externalDataRecordRef = func_get_args()[0];

		$this->externalDataRecords = $this->shgdprdm_getExternalRecords();

		$this->validRecord = $this->shgdprdm_validateRecordRef();

		if(!$this->validRecord){
			throw new Exception('Error! Invalid Record.');
		}
		else{
			$this->localUpdatedRef = $this->shgdprdm_updateLocalStorage();
		}

		if(!$this->localUpdatedRef){
			throw new Exception('Error! Can\'t write to Local Database at this time.');
		}
		else{
			$this->shgdprdm_updateExternalStorage();
		}

	}

	public function shgdprdm_getUpdatedRef(){
		return $this->localUpdatedRef;
	}

	public function shgdprdm_getAdminAction(){
		return $this->adminReExport;
	}

	public function shgdprdm_getRecordEmail(){
		return $this->adminReExportEmail;
	}

	private function shgdprdm_getExternalRecords(){
		return $records = shgdprdm_getReviewHistoryExternalData();
	}

	private function shgdprdm_updateExternalStorage(){
		$this->updateExtData['shgdprdm_uwhen'] = date('Y-m-d H:i:s');
		// Update Dynamo DB
		if(shgdprdm_setReviewHistoryExternalData( $this->updateExtData )){
			return TRUE;
		}
		return FALSE;
	}

	private function shgdprdm_convertOrdersToEmail( $orderIdsArray ){
		$convertToEmail = array();
		// $html.= "<td>";
		foreach( $orderIdsArray as $orderId ){
			// $order = wc_get_order(  $orderId  );
			$order = (function_exists('wc_get_order') ? wc_get_order(  $orderId  ) : '');
			if( $order ){
				$orderEmail = $order->get_billing_email();
				if( $orderEmail ){
					if( !in_array( $orderEmail, $convertToEmail ) ){
						array_push( $convertToEmail, $orderEmail );
					}
				}
				else{
					array_push( $convertToEmail, $orderId );
				}
			}
			else{
				array_push( $convertToEmail, $orderId );
			}
		}
		if(count($convertToEmail) < 1){
			return 'Unknown';
		}
		else if(count($convertToEmail) > 1){
			$return = 'Incomplete Data: ';
			foreach($convertToEmail as $reference){
				if( !is_email($reference) ){
					$return .= ' | Unknown Order: '.$reference;
				}
				else{
					$return .= ' | Email-Ref: '.$reference;
				}
			}
			return rawurlencode($return);
		}
		else{
			return $convertToEmail[0];
		}
	}

	private function shgdprdm_validateRecordRef(){
		// $returnData = array();
		$returnT = array();
		$returnA = array();

		foreach($this->externalDataRecords['recordRefs'] as $rIndex => $rValue){
			if($this->externalDataRecordRef == $rValue){
				// wp_die( print_r( $this->externalDataRecords['data'][$rIndex][4] ) );
				// $returnData[$rIndex] =  $this->externalDataRecords['data'][$rIndex];
				// $returnA[$rIndex] = $this->externalDataRecords['data'][$rIndex][4];
				$returnA[$rIndex] =  shgdprdm_mapLegacyRefs($this->externalDataRecords['data'][$rIndex][4]);
				$returnT[$rIndex] =  $this->externalDataRecords['data'][$rIndex][5];
			}
		}
		// wp_die( print_r( $returnData ) );
		$maxT = '';
		$maxA = '';
		$maxTIndex = array();
		$maxAIndex = array();
		if( !empty($returnT) ){
			$maxT = max( $returnT );
			$maxTIndex = array_keys( $returnT, $maxT );
			if( count($maxTIndex) == 1 ){
				$tRef = shgdprdm_mapLegacyRefs($this->externalDataRecords['data'][$maxTIndex[0]][4]);

				$maxA = max( $returnA );
				$maxAIndex = array_keys( $returnA, $maxA );
				$aRef = shgdprdm_mapLegacyRefs($this->externalDataRecords['data'][$maxAIndex[0]][4]);

				if($tRef == $aRef){
					// wp_die('Ref 1: '.$maxT.' | '.print_r($tRef));
					return $this->externalDataRecords['data'][$maxTIndex[0]];
				}

				// $legacyRefs = array(4,5,6,9);
				if($aRef > $tRef){
					return $this->externalDataRecords['data'][$maxAIndex[0]];
					// if( in_array( $tRef, $legacyRefs ) ){ // Legacy
					// 	wp_die('Ref 3: '.$maxA.' | '.serialize($aRef));
					// 	return $this->externalDataRecords['data'][$maxAIndex[0]];
					// }
					// if( $aRef == 10	){
					// 	wp_die('Ref 2: '.$maxA.' | '.serialize($aRef));
					// 	return $this->externalDataRecords['data'][$maxAIndex[0]];
					// }
					//
					// wp_die('Ref 4: '.$maxT.' | '.serialize($tRef));

				}
				return $this->externalDataRecords['data'][$maxTIndex[0]];
				// wp_die( print_r( $this->externalDataRecords['data'][$maxIndex[0]] ) );
				// return $this->externalDataRecords['data'][$maxIndex[0]];
			}
			// Fallback incase there are multiple records with the same time stamp (Should not happen)
			else if(count($maxTIndex) > 1){
				$maxA = max( $returnA );
				$maxAIndex = array_keys( $returnA, $maxA );
				if( count($maxAIndex) == 1 ){
					// wp_die( "ACTION: ".print_r( $this->externalDataRecords['data'][$maxIndex[0]] ) );
					return $this->externalDataRecords['data'][$maxAIndex[0]];
				}
				wp_die("Oops! Something has gone wrong. Error SYN_001");
				return FALSE;
			}
			wp_die("Oops! Something has gone wrong. Error SYN_002");
			return FALSE;
		}
		wp_die("Oops! Something has gone wrong. Error SYN_003");
		return FALSE;
	}

	private function shgdprdm_updateLocalStorage(){

		if( ( substr( (string)$this->validRecord[4], -1) == 's' ) ){
			$uWhat = shgdprdm_mapLegacyRefs($this->validRecord[4]);
		}
		else{
			$uWhat = shgdprdm_mapLegacyRefs($this->validRecord[4]).'s';
		}
		$this->updateExtData = array(
			'shgdprdm_rid' => $this->validRecord[6],
			'shgdprdm_awhat' => $this->validRecord[0],
			'shgdprdm_uwho' => $this->validRecord[1],
			'shgdprdm_awho' => $this->validRecord[2],
			'shgdprdm_awhen' => $this->validRecord[3],
			'shgdprdm_uwhat' => $uWhat,
			// 'shgdprdm_uwhat' => $this->validRecord[4].'s',
			// 'shgdprdm_uwhen' => $updateTime
		);
		// wp_die(print_r($this->validRecord));
		// shgdprdm_updateActionsHistory($expt, $uid, $aid, $acv = NULL, $rand = NULL, $avt = '0000-00-00 00:00:00', $sync = '', $key = NULL){
		// Only allow Admin to Carry Out a Delete if its confirmed that the user has previously compelted a Delte OR the Admin has previously carried out a delete

		$uWhat = shgdprdm_mapLegacyRefs($this->validRecord[4]);
		if(
			( $this->validRecord[0] == 6 || $this->validRecord[0] == '6' ) &&
			( $uWhat == 10 || $uWhat == '10' || $uWhat == 107 || $uWhat == '107')
		){
			$user = (get_user_by('id',$this->validRecord[1]) );
			if($user){
				$this->adminReExport = TRUE;
				$this->adminReExportEmail = (is_array($this->validRecord[1])?''.$this->shgdprdm_convertOrdersToEmail($this->validRecord[1]).'':$this->validRecord[1]);
				// wp_die('Sync 1');
				$ref = shgdprdm_updateActionsHistory(
					$this->validRecord[0],  // Export Type
					// (is_array($this->validRecord[1])?'Guest@guest.guest':$this->validRecord[1]), // User Email / Guest
					(is_array($this->validRecord[1])?''.$this->shgdprdm_convertOrdersToEmail($this->validRecord[1]).'':$this->validRecord[1]), // User Email / Guest

					get_current_user_id(), // Admin ID
					100, // User Action Taken ( Indicator that this is a delete that needs to be re-run)
					'', 									 // Random String
					$this->validRecord[5], // User Action Timestamp
					1 , 									 // "Sync" indicator = true
					$this->validRecord[6] // Record Reference
				);
				return $ref;
			}
			else{
				// wp_die('Sync 2');
				$ref = shgdprdm_updateActionsHistory(
					$this->validRecord[0],  // Export Type
					// (is_array($this->validRecord[1])?'Guest@guest.guest':$this->validRecord[1]), // User Email / Guest
					(is_array($this->validRecord[1])?''.$this->shgdprdm_convertOrdersToEmail($this->validRecord[1]).'':$this->validRecord[1]), // User Email / Guest

					get_current_user_id(), // Admin ID
					102 , // User Action Taken ( Indicator that this is a delete does not need to be re-run)
					'', 									 // Random String
					$this->validRecord[5], // User Action Timestamp
					1 , 									 // "Sync" indicator = true
					$this->validRecord[6] // Record Reference
				);
				return $ref;
			}
		}
		// wp_die(shgdprdm_mapLegacyRefs($this->validRecord[4]));
		$ref = shgdprdm_updateActionsHistory(
			$this->validRecord[0],  // Export Type
			( is_array( $this->validRecord[1] ) ? ''.$this->shgdprdm_convertOrdersToEmail( $this->validRecord[1] ) : $this->validRecord[1] ), // User Email / Guest
			get_current_user_id(), // Admin ID
			$uWhat, // User Action Taken
			'', 									 // Random String
			$this->validRecord[5], // User Action Timestamp
			1 , 									 // "Sync" indicator = true
			$this->validRecord[6] // Record Reference
		);
		// wp_die($ref);
		return  $ref;
	}


  /**
   * Retrieve customer’s data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function shgdprdm_getActivityHistory( $per_page = 5, $page_number = 1) {

    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}shgdprdm_history";

		// if user is searching
		if(!empty( $_REQUEST['s'] )){
        $sql .= " WHERE (actionID LIKE '%{$_REQUEST['s']}%' OR actionType LIKE '%{$_REQUEST['s']}%'
					OR userID LIKE '%{$_REQUEST['s']}%' OR userEmail LIKE '%{$_REQUEST['s']}%'
					OR actionedBy LIKE '%{$_REQUEST['s']}%' ".shgdprdm_reviewLikeType($_REQUEST['s']).shgdprdm_reviewLikeAdmin($_REQUEST['s'])." ) ";
    }

		// If column sort is clicked
    if ( ! empty( $_REQUEST['orderby'] ) ) {
      $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
      $sql .= ! empty( $_REQUEST['order'] ) ? ' '.esc_sql( $_REQUEST['order'] ).' ' : ' ASC';
    }
		else{
			$sql .= ' ORDER BY actionTimestamp DESC' ;
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

// echo "<br>SQL: ".$sql;
    $result = $wpdb->get_results( $sql, 'ARRAY_A' );

		$result = shgdprdm_reviewPrettyPrint($result);
    return $result;
  }

	/**
   * Retrieve missing data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function shgdprdm_getMissingHistory( $arrayDifferenceIDs = array(), $per_page = 5, $page_number = 1) {

    if(!$arrayDifferenceIDs || count($arrayDifferenceIDs) < 1 ){
			return FALSE;
		}

		$sqlIn = '';
		$sqlIn .= ' WHERE actionID IN ( ';
		$sqlIn .= implode(',',$arrayDifferenceIDs);
		$sqlIn .= ' )';

		global $wpdb;

    $sql = "SELECT actionID, actionType, userID, actionedBy, disasterSync FROM {$wpdb->prefix}shgdprdm_history ".$sqlIn;
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

    $result = $wpdb->get_results( $sql, 'ARRAY_A' );

		$result = shgdprdm_reviewPrettyPrint($result);
    return $result;
  }


  /**
 * Returns the count of records in the database.
 *
 * @return null|string
 */
  public static function shgdprdm_recordCount() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}shgdprdm_history";

		// Add search condition if user is searching
		if(!empty( $_REQUEST['s'] )){
        $sql .= " WHERE (actionID LIKE '%{$_REQUEST['s']}%' OR actionType LIKE '%{$_REQUEST['s']}%'
					OR userID LIKE '%{$_REQUEST['s']}%' OR userEmail LIKE '%{$_REQUEST['s']}%'
					OR actionedBy LIKE '%{$_REQUEST['s']}%' ".shgdprdm_reviewLikeType($_REQUEST['s']).shgdprdm_reviewLikeAdmin($_REQUEST['s'])." ) ";
    }
    return $wpdb->get_var( $sql );
  }

	public static function shgdprdm_recordRefernces() {
    global $wpdb;

    $sql = "SELECT actionID FROM {$wpdb->prefix}shgdprdm_history";

    $result =  $wpdb->get_results( $sql, 'ARRAY_N' );
		foreach($result as $rInd => $rVal){
			$result[$rInd] = $rVal[0];
		}
		self::$localDataRefs =  $result;
  }

  /** Text displayed when no post data is available */
  public function no_items() {
    _e( 'No records avaliable.', 'sp' );
  }

	public function shgdprdm_getRecordCount(){
		return self::$total_items;
	}

  /**
   * Render a column when no column specific method exists.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
    public function column_default( $item, $column_name ) {
      switch ( $column_name ) {
        case 'actionID':
        case 'actionType':
				case 'userID':
				case 'userEmail':
				case 'actionedBy':
				case 'actionTimestamp':
				case 'actionVerify':
          return $item[ $column_name ];
        default:
          return print_r( $item, true ); //Show the whole array for troubleshooting purposes
      }
    }

    /**
   *  Associative array of columns
   *
   * @return array
   */
  function get_columns() {
		$columns = array(
			'actionID' => __( 'Ref' ),
			'actionType' => __( 'Type' ),
			'userID' => __( 'User Ref' ),
			'userEmail' => __( 'User Email' ),
			'actionedBy' => __( 'Actioned By (Admin)' ),
			'actionTimestamp' => __( 'Admin Timestamp' ),
			'actionVerify' => __( 'User Action Taken' ),
    );
    return $columns;
  }

  /**
 * Columns to make sortable.
 *
 * @return array
 */
  public function get_sortable_columns() {
    $sortable_columns = array(
      'userID' => array( 'userID', true ),
      'actionType' => array( 'actionType', false )
    );

    return $sortable_columns;
  }
	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns()
	{
			return array();
	}
  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items() {

		$per_page = $this->get_items_per_page( 'posts_per_page', 10 );
		$current_page = $this->get_pagenum();
		self::$total_items  = self::shgdprdm_recordCount();

		// Reset the pagination back to page-1 if searching
		if(!empty( $_REQUEST['s'] )){
			$current_page = 1;
		}

		$this->set_pagination_args( array(
      'total_items' => self::$total_items, // total number of items
      'per_page'    => $per_page // items to show on a page
    ) );

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->shgdprdm_getActivityHistory($per_page, $current_page);
		if($data){
			usort( $data, array( &$this, 'sort_data' ) );
		}


		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
  }

	protected function shgdprdm_compareRecords(){
		// Get Lcoal Records References (This sets the class variable)
		self::shgdprdm_recordRefernces();

		// Set the External Record Refs
		foreach(self::$externalDataRecords as $record){
			array_push( self::$externalRecordRefs , $record[6] );
		}

		self::$extDiffRefs = array_diff(self::$externalRecordRefs, self::$localDataRefs);
		self::$localDiffRefs = array_diff(self::$localDataRefs,self::$externalRecordRefs);

		if(!empty(self::$extDiffRefs) || !empty(self::$localDiffRefs)){
			return TRUE;
		}
		return FALSE;
	}

	protected function shgdprdm_makeExtRecordSyncButton($ref){
		$page = esc_url( admin_url( 'admin.php').'?page=seahorse_gdpr_data_manager_plugin'); ?>
		<form id="shgdprdm_sync_ext_record_<?php echo $ref; ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>?action=shgdprdm_external_sync">
			<?php wp_nonce_field( $page, 'shgdprdmsd_nonce' ); ?>
			<input type="hidden" id="shgdprdm_sync_ext_record_ref_<?php echo $ref; ?>" name ="shgdprdm_sync_ext_record_ref_<?php echo $ref; ?>" value="<?php echo $ref; ?>"/>
			<?php
				$attr = array( 'id' => 'shgdprdm_sync_ext_record_submit_'.$ref.'', 'title' => 'Sync Record '.$ref.'');
				submit_button('Sync Record', 'primary', 'sync_ext_record', false, $attr);
	}




	public function shgdprdm_reviewHistoryTable() { ?>

		<?php
		self::prepare_items();
		if( self::shgdprdm_compareRecords() ){

			?>
			<div>
				<div class="shgdprdm-notice notice-error" style="border-left: 4px solid #dc3232;">
					<h2 style="color:#dc3232;font-weight:600;margin-right:5px;"><span class="dashicons dashicons-warning" style="margin-right:5px;"></span>Warning!</h2>
					<p style="font-weight:600;">There are discrepancies between your local records and your remote recovery records.</p>
					<ul style="list-style: disc;list-style-position: inside;">
						<li>Local Storage: <?php echo self::$total_items;?> records</li>
						<li>Remote Recovery: <?php echo self::$externalDataCount;?> records</li>
					</ul>

				<br>
				<h2>Missing Record References:</h2>

				<?php
				if(!empty(self::$extDiffRefs)){
				?>
					<h3>Records found in Remote Backup but NOT stored in Local Database</h3>
					<table class='wp-list-table widefat fixed striped posts'>
						<tr>
							<th>Ref</th>
							<th>Type</th>
							<th>User Ref. / Order ID's</th>
							<th>Actioned By</th>
							<th>Restore / Synchronise</th>
						</tr>
						<?php
						foreach(self::$externalDataRecords as $record){
							array_push( self::$externalRecordRefs , $record[6] );
							if( isset( $record[6] ) && !in_array( $record[6], self::$localDataRefs ) ) { ?>
								<tr>
									<td><?php echo $record[6];?></td>
									<td><?php echo shgdprdm_reviewConvertType($record[0]);?></td>
									<td>
										<?php
										if(  is_array( $record[1] ) ){
			                echo "Guest Order ID's: ";
			                foreach( $record[1] as $orderId){
			                  echo $orderId.' | ';
			                }
			              }
										else{
											$email = get_user_by( 'id', $record[1] );
											if($email){
												echo $email->user_email;
											}
											else{
												echo $record[1];
											}
										}
										?>
									</td>
									<td><?php echo shgdprdm_reviewConvertAdmin($record[2]);?></td>
									<td>
										<?php
										if($record[0] == 5){
											echo self::shgdprdm_makeExtRecordSyncButton($record[6]);
										}
										else{
											echo 'Action Required';
										}
										?>
									</td>
								</tr>
							<?php
							}
						} ?>
					</table>
				<?php
				}
				?>


			<!-- In the local DB but not in the Remote DB -->

			<?php $localDiffs = array_diff(self::$localDataRefs,self::$externalRecordRefs);
			if( !empty( self::$localDiffRefs )){
				$missingData = self::shgdprdm_getMissingHistory( self::$localDiffRefs ); ?>
				<br>
				<h3>Records stored in Local Database but NOT found in Remote Backup</h3>

				<table class='wp-list-table widefat fixed striped posts'>
					<tr>
						<th>Ref</th>
						<th>Type</th>
						<th>User Ref. / Order ID's</th>
						<th>Actioned By</th>
					</tr>
				<?php foreach($missingData as $mData){ ?>
					<tr>
						<td><?php echo $mData['actionID'];?></td>
						<td><?php echo $mData['actionType'];?></td>
						<td><?php echo $mData['userID'];?></td>
						<td><?php echo $mData['actionedBy'];?></td>
					</tr>
					<?php }
			} ?>
		</table>
			<br>
			</div> <!-- Close Notification Panel-->
			<br>
		</div>

		<hr>
		<h2>Local Records:</h2>
		<?php
		}
		?>

		<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
          <form method="post">
            <?php
						// self::prepare_items();
            self::search_box('search', 'search_id');
            self::display();
            ?>
          </form>
        </div>
      </div>
			<div id="postbox-container-1" class="postbox-container mdv-profile-container">
				<div class="meta-box-sortables">

					<div class="postbox">

						<button type="button" class="handlediv" aria-expanded="true" >
							<span class="screen-reader-text">Toggle panel</span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<!-- Toggle -->

						<h2 class="hndle"><span><?php esc_attr_e(
									'Seahorse - GDPR Data Manager', 'WpAdminStyle'
								); ?></span></h2>

						<div class="inside">
							<p class="mdv-center"><img src="<?php echo plugins_url( 'assets/images/shgdprdm_logo-icon.png', dirname(__FILE__) );?>" alt="Seahorse Logo"/></p>
							<p class="mdv-center"><?php echo SHGDPRDM_Seahorse_Profile_Text;?></p>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->
    </div>

    <br class="clear">
  </div>
<?php }


} // end of class
} // end of if_exists
?>
