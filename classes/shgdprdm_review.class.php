<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

define('SHGDPRDM_PRINTSCREEN', FALSE);
if ( ! class_exists( 'SHGdprdm_NativeListTable' ) ) {
	require_once( 'shgdprdm_wpListClass.class.php' );
}

if(!class_exists('SHGdprdm_HistoryList')){
class SHGdprdm_HistoryList extends SHGdprdm_NativeListTable {

	protected static $total_items;
	private $externalDataCount;
	private $externalDataRecords;
	private $localDataRefs;
	private $localDataStatus;
	private $localSyncStatus;
	// protected static $externalRecordRefs;
	private $externalRecordRefs;
	private $extDiffRefs;
	private $localDiffRefs;
	private $localDataSyncIssueRefs;


	/** Class constructor */
	public function __construct() {

		// parent::__construct();

    parent::__construct( [
			'singular' => __( 'Post', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Posts', 'sp' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		] );

		if(count(func_get_args()) !== 1){
      throw new Exception('Error! Action cannot be performed.');
    }
    $this->externalDataCount = count(func_get_args()[0]);
		$this->externalDataRecords = func_get_args()[0];
		if(!empty($this->externalDataRecords)){
			$this->updateHistoricalRefs();
		}
		$this->localDataRefs = array();
		$this->localDataStatus = array();
		$this->localSyncStatus = array();
		// self::$externalRecordRefs = array();
		$this->externalRecordRefs = array();
		$this->extDiffRefs = array();
		$this->localDiffRefs = array();
		$this->localDataSyncIssueRefs = array();
	}


  /**
   * Retrieve customer’s data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  private function shgdprdm_getActivityHistory( $per_page = 5, $page_number = 1) {

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
  private function shgdprdm_getMissingHistory( $arrayDifferenceIDs = array(), $per_page = 5, $page_number = 1) {

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
  private function shgdprdm_recordCount() {
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

	// public static function shgdprdm_recordRefernces() {
  //   global $wpdb;
	//
  //   $sql = "SELECT actionID FROM {$wpdb->prefix}shgdprdm_history";
	//
  //   $result =  $wpdb->get_results( $sql, 'ARRAY_N' );
	// 	foreach($result as $rInd => $rVal){
	// 		$result[$rInd] = $rVal[0];
	// 	}
	// 	$this->localDataRefs =  $result;
  // }

	private function shgdprdm_recordRefernces() {
    $resultStatus = array();
		$resultDisasterStatus = array();
		global $wpdb;

    $sql = "SELECT actionID, actionVerify, disasterSync FROM {$wpdb->prefix}shgdprdm_history";

    $result =  $wpdb->get_results( $sql, 'ARRAY_N' );
		// print_r('<br>Resutls: <br>');
		// print_r($result);
		foreach($result as $rInd => $rVals){
			$result[$rInd] = $rVals[0];
			$resultStatus[$rVals[0]] = shgdprdm_mapLegacyRefs($rVals[1]);
			$resultDisasterStatus[$rVals[0]] = $rVals[2];
		}
		$this->localDataRefs =  $result;
		$this->localDataStatus =  $resultStatus;
		$this->localSyncStatus =  $resultDisasterStatus;
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

			if( $item['actionVerify'] == 'Record Synced By Admin'){
				$wrapStart="<span class='shgdprdm-admin-history-action'>";
				$wrapEnd="</span>";
			}
			else if( $item['actionVerify'] == 'User Data Deleted By User. (Record Synced By Admin - No Data to Delete)'){
				$wrapStart="<span class='shgdprdm-admin-history-action'>";
				$wrapEnd="</span>";
			}
			else if( $item['actionVerify'] == 'Admin Action Pending (Synced Record)' ){
				$wrapStart="<span class='shgdprdm-admin-pending'>";
				$wrapEnd="</span>";
			}
			else if( $item['actionVerify'] == 'Link Actioned by Admin (Sync Record)'){
				$wrapStart="<span class='shgdprdm-admin-pending'>";
				$wrapEnd="</span>";
			}
			else if( $item['actionVerify'] == 'Delete Button Actioned by Admin'){
				$wrapStart="<span class='shgdprdm-admin-pending'>";
				$wrapEnd="</span>";
			}
			else if( $item['actionVerify'] == 'Record Synced & User Data Deleted By Admin'){
				$wrapStart="<span class='shgdprdm-admin-history-action'>";
				$wrapEnd="</span>";
			}
			else if( $item['actionVerify'] == 'User Delete - Error'){
				$wrapStart="<span class='shgdprdm-admin-pending'>";
				$wrapEnd="</span>";
			}
			else if( $item['actionVerify'] == 'Admin Delete - Error'){
				$wrapStart="<span class='shgdprdm-admin-pending'>";
				$wrapEnd="</span>";
			}

			else if( $item['disasterSync'] == 1 ){
				$wrapStart="<span class='shgdprdm-admin-history-action'>";
				$wrapEnd="</span>";
			}
			else{
				$wrapStart = '';
				$wrapEnd = '';
			}
			switch ( $column_name ) {
        case 'actionID':
        case 'actionType':
				case 'userID':
				case 'userEmail':
				case 'actionedBy':
				case 'actionTimestamp':
				case 'actionVerify':
				case 'actionVerifyTimestamp':
				case 'disasterSync':
          return $wrapStart.$item[ $column_name ].$wrapEnd;
					// return $item[ $column_name ];
        default:
					return '-';
          // return print_r( $item, true ); //Show the whole array for troubleshooting purposes
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
			'actionVerifyTimestamp' => __( 'User Action Timestamp' ),
			'disasterSync' => __( 'Sync Record' ),
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
		$hiddenColumns = array('disasterSync',);
		return $hiddenColumns;
	}
  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items() {

		$per_page = $this->get_items_per_page( 'posts_per_page', 10 );
		$current_page = $this->get_pagenum();
		self::$total_items  = $this->shgdprdm_recordCount();

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

	private function shgdprdm_compareRecords(){
		// Get Lcoal Records References (This sets the class variable)
		$this->shgdprdm_recordRefernces();

		// Set the External Record Refs
		foreach($this->externalDataRecords as $record){
			// array_push( self::$externalRecordRefs , ((gettype($record[6]) == 'double' || gettype($record[6]) == 'float')?(int)round($record[6]):$record[6]) );
			array_push( $this->externalRecordRefs , ((gettype($record[6]) == 'double' || gettype($record[6]) == 'float')?(int)round($record[6]):$record[6]) );
			// array_push( self::$externalRecordRefs , $record[6] );
		}

		$this->extDiffRefs = array_diff($this->externalRecordRefs, $this->localDataRefs);
		$this->localDiffRefs = array_diff($this->localDataRefs,$this->externalRecordRefs);
		// $this->extDiffRefs = array_diff(self::$externalRecordRefs, $this->localDataRefs);
		// $this->localDiffRefs = array_diff($this->localDataRefs,self::$externalRecordRefs);

		if(!empty($this->extDiffRefs) || !empty($this->localDiffRefs)){
			return TRUE;
		}
		return FALSE;
	}

	private function shgdprdm_makeExtRecordSyncButton($recordIndex, $ref, $type, $userAction, $userRef){ ?>
		<form id="shgdprdm_sync_ext_record_<?php echo $ref; ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>?action=shgdprdm_external_sync">
			<?php wp_nonce_field( 'shgdprdm_external_sync', 'shgdprdmexsy_nonce' ); ?>
			<input type="hidden" id="shgdprdm_sync_ext_record_ref[<?php echo $recordIndex;?>]" name ="shgdprdm_sync_ext_record_ref[<?php echo $recordIndex;?>]" value="<?php echo $ref; ?>"/>
			<?php
				$attr = array( 'id' => 'shgdprdm_sync_ext_record_submit_'.$ref.'', 'title' => 'Sync Record '.$ref.'');
				// submit_button(($type==5?'Sync Record':'Sync & Action Record'), ($type==5?'primary':'button  shgdprdm-usr-btn shgdprdm-usr-btn-delete shgdprdm_delete_user'), 'shgdprdm_sync_ext_record_submit_'.$ref.'', false, $attr);
				if( $type==5 || ( $type==6 && ( $userAction != '10' && $userAction != '107' ) ) ){
					$btnNm = 'Sync Record';
					$btnCl = 'primary';
				}
				else if( ($type==6 && ( $userAction == '10' || $userAction == '107' ) ) ){
					$user = get_user_by('ID', $userRef);
					if($user){
						$btnNm = 'Sync & Action Record';
						$btnCl = 'button  shgdprdm-usr-btn shgdprdm-usr-btn-delete shgdprdm_delete_user';
					}
					else{
						$btnNm = 'No User Data Available. Sync Record';
						$btnCl = 'primary';
						$attr['title'].= '. There is no User Data in the Database relating to this User Reference Number.';
					}
				}
				else{
					$btnNm = 'Sync & Action Record';
					$btnCl = 'button  shgdprdm-usr-btn shgdprdm-usr-btn-delete shgdprdm_delete_user';
				}
				submit_button($btnNm, $btnCl, 'shgdprdm_sync_ext_record_submit_'.$ref.'', false, $attr);

	}




	public function shgdprdm_reviewHistoryTable() { ?>

		<?php
		self::prepare_items();
		if( $this->shgdprdm_compareRecords() ){ ?>
			<div>
				<div class="shgdprdm-notice notice-error" style="border-left: 4px solid #dc3232;">
					<h2 style="color:#dc3232;font-weight:600;margin-right:5px;"><span class="dashicons dashicons-warning" style="margin-right:5px;"></span>Warning!</h2>
					<p style="font-weight:600;">There are discrepancies between your local records and your remote recovery records.</p>
					<ul style="list-style: disc;list-style-position: inside;">
						<li>Local Storage: <?php echo self::$total_items;?> records</li>
						<li>Remote Recovery: <?php echo $this->externalDataCount;?> records</li>
					</ul>

				<br>
				<h2>Missing Record References:</h2>

				<?php
				if(!empty($this->extDiffRefs)){
					echo $this->shgdprdm_syncRecordsTableTitle('EXTERNAL');
					echo $this->shgdprdm_syncRecordsTableOpen();
					echo $this->shgdprdm_syncRecordsTableHeader();
					// print_r($this->externalDataRecords);
					$errorRefs = $this->shgdprdm_getLatestExternalRecord();
					if(!$errorRefs){
						print_r('<br>Duplicates Removed<br>');
					}
					else{
						print_r('<br>Problems<br>');
						print_r($errorRefs);
					}
					// print_r($this->externalDataRecords);
					// if(){
					//
					// }

					// $max = max( $test );
					// $maxIndex = array_keys($test,$max);
					// if(count($mIndex) == 1 ){
					// 	$lastIndex = $mIndex[0];
					// }
					// else{
					// 	$lastIndex = serialize($maxIndex);
					// }
					// wp_die( print_r( $max.' | '.$lastIndex ) );

					foreach($this->externalDataRecords as $recordIx => $record){
						// $record[4] = shgdprdm_mapLegacyRefs($record[4]);



						// array_push( self::$externalRecordRefs , (string)$record[6] );
						// if( isset( $record[6] ) && $record[6] != NULL && !empty($record[6]) ){
						// 	array_push( self::$externalRecordRefs , ((gettype($record[6]) == 'double' || gettype($record[6]) == 'float' || is_float(round($record[6])))?'Test':'Problem') );
						// }
						// array_push( self::$externalRecordRefs , ((gettype($record[6]) == 'double' || gettype($record[6]) == 'float')?(int)round($record[6]):$record[6]) );

						// if( isset( $record[6] ) && !in_array( $record[6], $this->localDataRefs ) ) {
						if( isset( $record[6] ) ) {
							$errorIdentified = FALSE;
							if( !empty($errorRefs) && in_array( $record[6], $errorRefs ) ){
								 $errorIdentified = TRUE;
							}
							if( !in_array( $record[6], $this->localDataRefs )  ){
								$row = $this->shgdprdm_syncRecordsTableRow($recordIx, $record, $errorIdentified);
								echo $row[0];
								if( $row[1] ){
									$this->shgdprdm_makeExtRecordSyncButton($recordIx, $record[6], $record[0], $record[4], $record[1]);
								}
								echo $this->shgdprdm_syncRecordsCellClose();
								echo $this->shgdprdm_syncRecordsRowClose();
							}
							if( in_array( $record[6], $this->localDataRefs ) ){
								if( array_count_values( $this->externalRecordRefs )[$record[6]] > 1 ){
									if( !isset( $this->localDataStatus[ $record[6] ] ) ){
										$row = $this->shgdprdm_syncRecordsTableRow($recordIx, $record, $errorIdentified);
										echo $row[0];
										// If an action button is to be generated
										if( $row[1] ){
											$this->shgdprdm_makeExtRecordSyncButton($recordIx, $record[6], $record[0], $record[4],$record[1]);
										}
										echo $this->shgdprdm_syncRecordsCellClose();
										echo $this->shgdprdm_syncRecordsRowClose();
									}
									if( isset( $this->localDataStatus[ $record[6] ] ) ){
										if( $this->localDataStatus[ $record[6] ] < $record[4] ){
											$isSync = ( substr( (string)$record[4], -1) == 's' ) ? TRUE: FALSE;
											if( !$isSync ){ // Not Sync Items
												$row = $this->shgdprdm_syncRecordsTableRow($recordIx, $record, $errorIdentified);
												echo $row[0];
												// If an action button is to be generated
												if( $row[1] ){
													$this->shgdprdm_makeExtRecordSyncButton($recordIx, $record[6], $record[0], $record[4], $record[1]);
												}
												echo $this->shgdprdm_syncRecordsCellClose();
												echo $this->shgdprdm_syncRecordsRowClose();
											}
											if( $isSync ){ // Sync Items
												if( $this->localSyncStatus[ $record[6] ] < 1 ){
													$row = $this->shgdprdm_syncRecordsTableRow($recordIx, $record, $errorIdentified);
													echo $row[0];
													// If an action button is to be generated
													if( $row[1] ){
														$this->shgdprdm_makeExtRecordSyncButton($recordIx, $record[6], $record[0], $record[4], $record[1]);
													}
													echo $this->shgdprdm_syncRecordsCellClose();
													echo $this->shgdprdm_syncRecordsRowClose();
												}
											}

										}
										// Testing
										if( $this->localDataStatus[ $record[6] ] > $record[4] ){
											$this->localDataSyncIssueRefs[ $record[6] ] = $record;
											// $row = $this->shgdprdm_syncRecordsTableRow($recordIx, $record, $errorIdentified);
											// echo $row[0];
											// // If an action button is to be generated
											// if( $row[1] ){
											// 	$this->shgdprdm_makeExtRecordSyncButton($recordIx, $record[6], $record[0], $record[4]);
											// }
											// echo $this->shgdprdm_syncRecordsCellClose();
											// echo $this->shgdprdm_syncRecordsRowClose();
										}
									}
								}
							}


							// if( isset( $record[6] ) &&
							// 	(
							// 		!in_array( $record[6], $this->localDataRefs ) ||
							// 		( in_array( $record[6], $this->localDataRefs ) && array_count_values( $this->externalRecordRefs )[$record[6]] > 1)
							//   )
							// ) {
							// 	if(
							// 		!in_array( $record[6], $this->localDataRefs ) ||
							// 		(
							// 			in_array( $record[6], $this->localDataRefs ) &&
							// 			(
							// 				( isset( $this->localDataStatus[ $record[6] ] ) && ( $this->localDataStatus[ $record[6] ] < $record[4]) ) ||
							// 				// ( isset( $this->localDataStatus[ $record[6] ] ) && ( $this->localDataStatus[ $record[6] ] > $record[4]) ) ||
							// 				( !isset( $this->localDataStatus[ $record[6] ]) )
							// 			)
							// 		)
							// 	){


									// if( isset( $this->localDataStatus[ $record[6] ] ) ){
									// 	print_r('<br><br>Record Status at Record: '.$record[6].'<br>');
									// 	print_r($this->localDataStatus[ $record[6] ]);
									// }


									//	echo $this->shgdprdm_syncRecordsTableRow($recordIx, $record);
								//}
						}
					}
					echo $this->shgdprdm_syncRecordsTableClose();
				}
				?>


				<!-- In the local DB but not in the Remote DB -->
				<?php $localDiffs = array_diff($this->localDataRefs,$this->externalRecordRefs);
				// $localDiffs = array_diff($this->localDataRefs,self::$externalRecordRefs);
				if( !empty( $this->localDiffRefs )){
					echo '<br>';
					echo $this->shgdprdm_makeTableMissingHistoryLocal();
				} ?>
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
            // self::search_box('search', 'search_id');
						$this->search_box('search', 'search_id'); // OVerride for standard class
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


							<!-- <p><?php esc_attr_e( SHGDPRDM_Seahorse_Profile_Text, 'WpAdminStyle' ); ?></p> -->
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

		// Check if there are multiple records & use the latest Version
		private function shgdprdm_getLatestExternalRecord(){
			if(SHGDPRDM_PRINTSCREEN === TRUE){
				print_r("Intentional Errors: <br>* 903<br>*<br><br>");
				// print_r('<br>All References: ');
				// print_r($this->externalRecordRefs);
			}

			// $problemRefs = array('909');
			$problemRefs = array();
			$multiRecordsTime = array();
			$multiRecordsAction = array();

			foreach($this->externalDataRecords as $recordIx => $record){
				if( isset( $record[6] ) && array_count_values( $this->externalRecordRefs )[$record[6]] > 1 ){
					if(!isset($multiRecordsTime[$record[6]])){
						$multiRecordsTime[$record[6]] = array( $recordIx => strtotime($record[5]) );
					}
					else{
						$multiRecordsTime[$record[6]][$recordIx] = strtotime($record[5]);
					}
					if(!isset($multiRecordsAction[$record[6]])){
						$multiRecordsAction[$record[6]] = array( $recordIx => ( substr( (string)$record[4], -1) == 's' ) ? (int)substr( $record[4] , 0 , -1) : $record[4] );
					}
					else{
						$multiRecordsAction[$record[6]][$recordIx] =  ( substr( (string)$record[4], -1) == 's' ) ? (int)substr( $record[4] , 0 , -1) : $record[4] ;
					}
				}
			}
			// print_r('<br>Full Multi Array: ');
			// print_r($multiRecordsTime);
			// print_r('<br>');
			// wp_die( print_r( $multiRecordsTime ) );
			if( !empty($multiRecordsTime) ){
				foreach($multiRecordsTime as $recordRef => $recordDataArray){
					// print_r('<br>Processing Record: ');
					// print_r($recordRef);
					$max = '';
					$maxIndex = array();
					if( !empty($recordDataArray) ){
						// print_r('<br>Full Removed Array: ');
						// print_r($recordDataArray);
						$maxTime = max( $recordDataArray );
						$maxTimeIndex = array_keys( $recordDataArray, $maxTime );

						if( !empty($multiRecordsAction[$recordRef]) ){
							if(SHGDPRDM_PRINTSCREEN === TRUE){
								print_r( ' Actions: ' );
							}
							$maxAction = max( $multiRecordsAction[$recordRef] );
							$maxActionIndex = array_keys( $multiRecordsAction[$recordRef], $maxAction );
							if( !empty($maxActionIndex) && count($maxActionIndex) > 1 ){
								$retainRef = array();
								foreach($maxActionIndex as $maRef ){

									if(SHGDPRDM_PRINTSCREEN === TRUE){
										print_r($this->externalDataRecords[$maRef][4]	);
										print_r( ' - ' );
									}

									if( substr( (string)$this->externalDataRecords[$maRef][4], -1) == 's' ){
										array_push($retainRef,$maRef);
									}
								}
								if( !empty($retainRef) && count($retainRef) == 1){
									$maxActionIndex = $retainRef;
								}
							}
							else if( !empty($maxActionIndex) && count($maxActionIndex) == 1 ){
								if(SHGDPRDM_PRINTSCREEN === TRUE){
									print_r($this->externalDataRecords[$maxActionIndex[0]][4]	);
									print_r( ' - ' );
								}
							}
							else{
								if(SHGDPRDM_PRINTSCREEN === TRUE){
									print_r( ' |?| ' );
								}
							}
							if(SHGDPRDM_PRINTSCREEN === TRUE){
								print_r( ' >>> Time: ' );
								if( !empty( $maxTimeIndex ) && count( $maxTimeIndex ) > 1 ){
									foreach($maxTimeIndex as $matRef ){
										print_r($this->externalDataRecords[$matRef][4]	);
										print_r( ' - ' );
									}
								}
								else if( !empty($maxTimeIndex) && count($maxTimeIndex) == 1 ){
									print_r($this->externalDataRecords[$maxTimeIndex[0]][4]	);
									print_r( ' - ' );
								}
								else{
									print_r( ' |%| ' );
								}
							}
						}
						if(SHGDPRDM_PRINTSCREEN === TRUE){
							if($maxTimeIndex === $maxActionIndex){
								print_r(" | ");
								print_r($recordRef);
								print_r(": MATCH! ");
								print_r($maxTimeIndex);
								print_r(" - ");
								print_r($maxActionIndex);
								// print_r("<br>");
							}
							else{
								print_r(" | ");
								print_r($recordRef);
								print_r(": NOT MATCHING! ");
								print_r($maxTimeIndex);
								print_r(" - ");
								print_r($maxActionIndex);
								// print_r("<br>");
							}
						}
						// if( count($maxTimeIndex) == 1 && count($maxActionIndex) == 1 ){
						if( !empty($maxTimeIndex) && !empty($maxActionIndex) ){
							// print_r($multiRecordsAction[$maxActionIndex]);

							// If the Index is the same, then remove all other Indecies
							// (i.e. Index of latest date == index of the highest action )
							if( count($maxTimeIndex) == 1 && count($maxActionIndex) == 1 ){
								if(SHGDPRDM_PRINTSCREEN === TRUE){
									print_r(" - Single REf for Both...");
								}
								if( $maxTimeIndex[0] === $maxActionIndex[0]){
									if(SHGDPRDM_PRINTSCREEN === TRUE){
										print_r(" - Time is same than Action...<br>");
									}
									foreach($recordDataArray as $multiRecordIndex => $multiReccordVal){
										if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxTimeIndex[0]) ){
											if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
												unset($this->externalDataRecords[$multiRecordIndex]);
											}
										}
									}
								}
							}
							else{
								if( $this->externalDataRecords[ $maxTimeIndex[0] ][4] === $this->externalDataRecords[ $maxActionIndex[0] ][4]){
									if(SHGDPRDM_PRINTSCREEN === TRUE){
										print_r(" - Time is same as Action...<br>");
									}
									foreach($recordDataArray as $multiRecordIndex => $multiReccordVal){
										if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxTimeIndex[0]) ){
											if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
												unset($this->externalDataRecords[$multiRecordIndex]);
											}
										}
									}
								}
								// If the Indecies are different, then take the higher action of either
								else if( $this->externalDataRecords[ $maxTimeIndex[0] ][4] > $this->externalDataRecords[ $maxActionIndex[0] ][4] ){
									if(SHGDPRDM_PRINTSCREEN === TRUE){
										print_r(" - Time is greater than Action...<br>");
									}
									foreach($recordDataArray as $multiRecordIndex => $multiReccordVal){
										if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxTimeIndex[0]) ){
											if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
												unset($this->externalDataRecords[$multiRecordIndex]);
											}
										}
									}
								}
								else if( $this->externalDataRecords[ $maxTimeIndex[0] ][4] < $this->externalDataRecords[ $maxActionIndex[0] ][4] ){
									if(SHGDPRDM_PRINTSCREEN === TRUE){
										print_r(" - Time is less than Action...<br>");
									}
									foreach($recordDataArray as $multiRecordIndex => $multiReccordVal){
										if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxActionIndex[0]) ){
											if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
												unset($this->externalDataRecords[$multiRecordIndex]);
											}
										}
									}
								}
								else{
									// print_r(" - Problem...<br>");
									array_push($problemRefs,$recordRef);
								}
							}
						}
						else{
							// print_r(" - Problem...<br>");
							array_push($problemRefs,$recordRef);
						}
						// else if(count($maxTimeIndex) > 1  && count($maxActionIndex) == 1 ){
						// 	print_r(" - Multiple Times & single Action...<br>");
						// 	foreach($recordDataArray as $multiRecordIndex => $multiReccordVal){
						//
						// 		if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxActionIndex[0]) ){
						// 			// print_r('<br>* Deleting Index: '.$multiRecordIndex);
						// 			if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
						// 				unset($this->externalDataRecords[$multiRecordIndex]);
						// 			}
						// 		}
						//
						// 	}
						// }
						// else if( count($maxActionIndex) > 1  && count($maxActionTimeIndex) == 1  ){
						//
						// 	if($maxActionIndex[0] === $maxActionTimeIndex[0]){
						// 		foreach($recordDataArray as $multiRecordIndex => $multiReccordVal){
						//
						// 			if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxTimeIndex[0]) ){
						// 				// print_r('<br>* Deleting Index: '.$multiRecordIndex);
						// 				if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
						// 					unset($this->externalDataRecords[$multiRecordIndex]);
						// 				}
						// 			}
						//
						// 		}
						// 	}
						// 	print_r(" - Multiple Actions & Single Time...<br>");
						// 	array_push($problemRefs,$recordRef);
						// 	foreach($recordDataArray as $multiRecordIndex => $multiReccordVal){
						//
						// 		if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxTimeIndex[0]) ){
						// 			// print_r('<br>* Deleting Index: '.$multiRecordIndex);
						// 			if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
						// 				unset($this->externalDataRecords[$multiRecordIndex]);
						// 			}
						// 		}
						//
						// 	}
						// }
						// else{
						// 	print_r(" - Problem...<br>");
						// 	array_push($problemRefs,$recordRef);
						// }

						// else if(count($maxTimeIndex) > 1{
						// 	// Change the Timestamp to the USer Action Record
						// 	// foreach($multiRecordsTime[$recordRef] as $multiRecordRef => $multiRecordTimestamp){
						// 	// 	$isSync = ( substr( (string)$this->externalDataRecords[$multiRecordRef][4], -1) == 's' ) ? TRUE : FALSE;
						// 	// 	if($isSync){
						// 	// 		$multiRecordsTime[$recordRef][$multiRecordRef] = (int)substr( $this->externalDataRecords[$multiRecordRef][4] , 0 , -1);
						// 	// 	}
						// 	// 	else{
						// 	// 		$multiRecordsTime[$recordRef][$multiRecordRef] = $this->externalDataRecords[$multiRecordRef][4];
						// 	// 	}
						// 	// }
						// 	if( !empty($multiRecordsTime) ){
						// 		if( !empty($multiRecordsTime[$recordRef]) ){
						// 			$maxTime = max( $multiRecordsTime[$recordRef] );
						// 			$maxTimeIndex = array_keys( $multiRecordsTime[$recordRef], $maxTime );
						// 			if( count($maxTimeIndex) == 1 ){
						// 				foreach($multiRecordsTime[$recordRef] as $multiRecordIndex => $multiReccordVal){
						// 					if( isset($multiRecordIndex) && ( $multiRecordIndex != $maxTimeIndex[0]) ){
						// 						if( isset( $this->externalDataRecords[$multiRecordIndex] ) ){
						// 							unset($this->externalDataRecords[$multiRecordIndex]);
						// 						}
						// 					}
						// 				}
						// 			}
						// 		}
						// 		else{
						// 			// This would indicate that there are 2x (or more) external records
						// 			// with the same timestamp AND the same User Action Reference
						// 			// Populate the record Reference into the "Problem Array" for Returning later
						// 			// References in the returned array should not allow a sync action but should trigger a notice?
						// 			array_push($problemRefs,$recordRef);
						// 		}
						// 	}
						// }
						// else{
						// 	// This would indicate that no Maximum value has been found
						// 	// in either timestamp OR the User Action Reference
						// 	// Populate the record Reference into the "Problem Array" for Returning later
						// 	// References in the returned array should not allow a sync action but should trigger a notice?
						// 	array_push($problemRefs,$record[6]);
						// }

					} // If Child Array not empty
					// else{
						// print_r('<br>No Records to Process');
					// }
					// print_r('<br>-------- End Processing Record ');
					// print_r($recordRef);
					// print_r(' --------<br>');
				} // For each parent Array item
				// return FALSE;
			} // If parent Array Not Empty
			if( empty($problemRefs) ){
				return FALSE;
			}

			return $problemRefs;
		}

		private function shgdprdm_syncRecordsTableTitle($table){
			$html = '';
			$html .= '<h3>';
			if(strtoupper($table) == 'EXTERNAL'){
				$html .= 'Records found in Remote Backup but NOT stored in Local Database';
			}
			else if(strtoupper($table) == 'LOCAL'){
				$html .= 'Records stored in Local Database but NOT found in Remote Backup';
			}
			else{
				$html .= 'Records Table';
			}
			$html .= '</h3>';

			return $html;
		}

		private function shgdprdm_syncRecordsTableOpen(){
			$html = '';
			$html .= '<table class="wp-list-table widefat fixed striped posts">';
			return $html;
		}
		private function shgdprdm_syncRecordsTableHeader(){
			$html = '';
			$html .= '<tr>
				<th>Ref</th>
				<th>Type</th>
				<th>User Ref. / Order ID\'s</th>
				<th>Actioned By</th>
				<th>User Action Taken</th>
				<th style="text-align:center;">Restore / Synchronise</th>
			</tr>';
			return $html;
		}
		private function shgdprdm_syncRecordsTableRow($rowRef, $rowRecord, $errorIdentified = FALSE){
			$html = '';
			$html .= '<tr>';
			$html .= '<td>'.$rowRecord[6].'</td>';
			$html .= '<td>'.shgdprdm_reviewConvertType($rowRecord[0]).'</td>';
			$html .= '<td>';
				if(  is_array( $rowRecord[1] ) ){
					$convertToEmail = $this->shgdprdm_convertRefToEmail($rowRecord[1]);
					$html .= 'Woo Commerce Guest Users:';
					foreach($convertToEmail as $orderEmail){
						$html .= '<br>'.$orderEmail;
					}
				}
				else{
					$email = get_user_by( 'id', $rowRecord[1] );
					if($email){
						$html .= $email->user_email;
					}
					else{
						$html .= $rowRecord[1];
					}
				}
			$html .= '</td>';
			$html .= '<td>'.shgdprdm_reviewConvertAdmin($rowRecord[2]).'</td>';
			$html .= '<td>'.shgdprdm_reviewConvertVerify($rowRecord[4]).'</td>';
			$html .= '<td class="shgdprdm_sync_button_container" align="center">';
			$makeBtn = FALSE;
				if($rowRecord[0] == 5 || $rowRecord[0] == 6){
					if( $errorIdentified ){
						$html .= '<span><strong>Inconsistant Data</strong><br>Please Contact the User prior to deleting these Data<br></em>Restore / Synchronise can not be performed at this time.</em></span>';
					}
					else if( is_array( $rowRecord[1] ) && count($convertToEmail) != 1 ){
						$html .= '<span><strong>Inconsistant Data</strong><br></em>Restore / Synchronise can not be performed at this time.</em></span>';
					}
					else if(  $rowRecord[0] == 6 && $rowRecord[4] == 8 ){
						$html .= '<span><strong>Unconfirmed User Data Delete</strong><br>Please Contact the User prior to deleting these Data<br></em>Restore / Synchronise can not be performed at this time.</em></span>';
					}
					else if( is_array( $rowRecord[1] ) && count($convertToEmail) == 1 && !is_email($convertToEmail[0]) ){
						$html .= '<span><strong>Missing Data</strong><br></em>Restore / Synchronise can not be performed at this time.</em></span>';
					}
					else{
						$makeBtn = TRUE;
						//return array($html,$makeBtn);
						// $this->shgdprdm_makeExtRecordSyncButton($rowRef, $rowRecord[6], $rowRecord[0], $rowRecord[4]);
					}
				}
				else{
					$html .= 'Action Required';
				}
			// $html .= '</td>';
			// $html .= '</tr>';

			return array($html,$makeBtn);
		}
		private function shgdprdm_syncRecordsCellClose(){
			return '</td>';
		}
		private function shgdprdm_syncRecordsRowClose(){
			return '</tr>';
		}
		private function shgdprdm_syncRecordsTableClose(){
			return '</table>';
		}

		private function shgdprdm_convertRefToEmail($ref){
			$convertToEmail = array();
			foreach($ref as $orderId){
				$order = (function_exists('wc_get_order') ? wc_get_order(  $orderId  ) : '');
				if( $order ){
					$orderEmail = $order->get_billing_email();
					if( $orderEmail ){
						if( !in_array( $orderEmail, $convertToEmail ) ){
							if($orderEmail == 'deleted@deleted.com'){
								$orderEmail.= '- Order Ref: '.$orderId;
							}
							array_push( $convertToEmail, $orderEmail );
						}
					}
					else{
						array_push( $convertToEmail, 'Unknown Email - Order Ref: '.$orderId );
					}
				}
				else{
					array_push( $convertToEmail, 'Unknown Email - Order Ref: '.$orderId );
				}
			}
			return $convertToEmail;
		} // End of fn

		private function shgdprdm_makeTableMissingHistoryLocal(){
			$html = '';

			$missingData = $this->shgdprdm_getMissingHistory( $this->localDiffRefs );

			// Testing
			// if( $mData['actionVerify'] > $record[4] ){
			// 	$row = $this->shgdprdm_syncRecordsTableRow($recordIx, $record, $errorIdentified);
			// 	echo $row[0];
			// 	// If an action button is to be generated
			// 	if( $row[1] ){
			// 		$this->shgdprdm_makeExtRecordSyncButton($recordIx, $record[6], $record[0], $record[4]);
			// 	}
			// 	echo $this->shgdprdm_syncRecordsCellClose();
			// 	echo $this->shgdprdm_syncRecordsRowClose();
			// }


			$html .= $this->shgdprdm_syncRecordsTableTitle('LOCAL');
			// <h3>Records stored in Local Database but NOT found in Remote Backup</h3>
			$html .= $this->shgdprdm_syncRecordsTableOpen();
			// print_r('903: Local => ');
			// print_r($this->localDataStatus[ 903 ]);
			// print_r(' | Remote => ');
			// print_r($this->localDataSyncIssueRefs[ 903 ][4]);
			// print_r('<br>');

			// print_r('909: Local => ');
			// print_r($this->localDataStatus[ 909 ]);
			// print_r(' | Remote => ');
			// print_r($this->localDataSyncIssueRefs[ 909 ][4]);
			// print_r('<br>');

			// print_r($this->localDataSyncIssueRefs);
			// print_r('<br>');
			// print_r($this->localDataSyncIssueRefs);
			// print_r('<br>');
			// print_r($this->localDataSyncIssueRefs[909]);
			// $this->externalDataRecords as $recordIx => $record
			$html .= '<tr>
					<th>Ref</th>
					<th>Type</th>
					<th>User Ref. / Order ID\'s</th>
					<th>Actioned By</th>
					<th></th>
				</tr>';

			foreach($missingData as $mData){
				$syncErrorRef = 'shgdprdm_SYE_001';
				$html .= '<tr>
					<td>'.$mData['actionID'].'</td>
					<td>'.$mData['actionType'].'</td>
					<td>'.$mData['userID'].'</td>
					<td>'.$mData['actionedBy'].'</td>
					<td class="shgdprdm_sync_button_container" align="center">'.SHGDPRDM_err_011.$syncErrorRef.'</span></td>
				</tr>';
			}
			if( !empty($this->localDataSyncIssueRefs) ){
				foreach($this->localDataSyncIssueRefs as $issueIx => $issueData){
					if( isset($this->localDataStatus[ $issueIx ]) ){
						if( !$this->shgdprdm_checkIfDeleteSyncLocal( $issueIx, $issueData ) ){
							$extVal = ( substr( (string)$issueData[4], -1) == 's' ) ? (int)substr( $issueData[4] , 0 , -1) : $issueData[4];
							if( $this->localDataStatus[ $issueIx ] > $extVal ){

								if(SHGDPRDM_PRINTSCREEN === TRUE){
									print_r('<br>');
									print_r($issueData[6]);
									print_r(' : ');
									print_r($this->localDataStatus[ $issueIx ]);
									print_r(' >>> ');
									print_r($issueData[4]);
									print_r(' >>> ');
									print_r($extVal);
								}


								$syncErrorRef = 'shgdprdm_SYE_002';
								$html .= '<tr>
									<td>'.$issueData[6].'</td>
									<td>'.shgdprdm_reviewConvertType($issueData[0]).'</td>
									<td>';
									if(  is_array( $issueData[1] ) ){
										$convertToEmail = $this->shgdprdm_convertRefToEmail($issueData[1]);
										$html .= 'Woo Commerce Guest Users:';
										foreach($convertToEmail as $orderEmail){
											$html .= '<br>'.$orderEmail;
										}
									}
									else{
										$email = get_user_by( 'id', $issueData[1] );
										if($email){
											$html .= $email->user_email;
										}
										else{
											$html .= $issueData[1];
										}
									}
									$html .= '</td>
									<td>'.shgdprdm_reviewConvertAdmin($issueData[2]).'</td>
									<td class="shgdprdm_sync_button_container" align="center">'.SHGDPRDM_err_011.$syncErrorRef.'</strong></span></td>
								</tr>';
							}
							// else{
							//
							// }
						}
					}
				}
			}

			// $this->localDataStatus[ $record[6] ]
			// if( isset($this->localDataSyncIssueRefs[$mData['actionID']]) ){
			// 	print_r('SET!');
			// 	if( $this->localDataStatus[ $mData['actionID'] ] > $this->localDataSyncIssueRefs[$mData['actionID']][4]){
			// 		$html .= '<tr>
			// 			<td>'.$mData['actionID'].'</td>
			// 			<td>'.$mData['actionType'].'</td>
			// 			<td>'.$mData['userID'].'</td>
			// 			<td>'.$mData['actionedBy'].' | '.$this->localDataStatus[ $mData['actionID'] ].'</td>
			// 			<td class="shgdprdm_sync_button_container" align="center">'.SHGDPRDM_err_011.$syncErrorRef.'</span></td>
			// 		</tr>';
			// 	}
			// 	}
			// }
			$html .= $this->shgdprdm_syncRecordsTableClose();
			return $html;

		} // End of fn

		private function shgdprdm_checkIfDeleteSyncLocal( $item, $data ){
			if($data[0] == 5){
				return FALSE;
			}
			if($data[0] == 6){
				if( $this->localDataStatus[ $item ] == 200 && $data[4] == '10s'){
					return TRUE;
				}
				return FALSE;
			}
			return TRUE;
		}


		/**
		 * Displays the search box. - OVER-RIDE FOR PARENT CLASS FUNCTION
		 *
		 * @since 3.1.0
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
				return;

			$input_id = $input_id . '-search-input';

			if ( ! empty( $_REQUEST['orderby'] ) )
				echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
			if ( ! empty( $_REQUEST['order'] ) )
				echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
			if ( ! empty( $_REQUEST['post_mime_type'] ) )
				echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
			if ( ! empty( $_REQUEST['detached'] ) )
				echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
	?>
	<p class="search-box">
		<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
		<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
		<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit', 'name' => 'shgdprdm-review-search-submit', 'formaction' => '' ) );
		?>
	</p>
	<?php
		}

		private function updateHistoricalRefs(){
			foreach($this->externalDataRecords as $recordIx => $record){
				$this->externalDataRecords[$recordIx][4] = shgdprdm_mapLegacyRefs($this->externalDataRecords[$recordIx][4]);
			}
		}

	} // end of class
} // end of if_exists
?>
