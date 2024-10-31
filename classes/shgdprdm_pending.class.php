<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
if ( ! class_exists( 'SHGdprdm_NativeListTable' ) ) {
	require_once( 'shgdprdm_wpListClass.class.php' );
}

if(!class_exists('SHGdprdm_PendingList')){
class SHGdprdm_PendingList extends SHGdprdm_NativeListTable {

	/** Class constructor */
	public function __construct() {

		// parent::__construct();

    parent::__construct( [
			'singular' => __( 'Post', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Posts', 'sp' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		] );

		self::shgdprdm_updateActivityTimeLapse();

	}

	private function shgdprdm_updateActivityTimeLapse(){
		global $wpdb;
		$table = $wpdb->prefix.'shgdprdm_history';
		$sql = "UPDATE {$table}	SET actionVerify = %d, actionTimestamp = %s
		WHERE DATE_SUB(actionTimestamp, INTERVAL -24 HOUR) < NOW()
		AND (actionVerify = %d OR actionVerify = %d OR actionVerify = %d)";

		// $wpdb->query( $wpdb->prepare( $sql,  3, date('Y-m-d H:i:s'), 1, 5 ) );
		$wpdb->query( $wpdb->prepare( $sql,  3, date('Y-m-d H:i:s'), 1, 5, 103 ) );
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

    $sql = "SELECT * FROM {$wpdb->prefix}shgdprdm_history
		WHERE (actionType = 5 OR  actionType = 6)
		AND (actionVerify = 1 OR actionVerify = 5 OR actionVerify = 103)";

		// if user is searching
		if(!empty( $_REQUEST['s'] )){
        $sql .= " and (actionID LIKE '%{$_REQUEST['s']}%' OR actionType LIKE '%{$_REQUEST['s']}%'
					OR userID LIKE '%{$_REQUEST['s']}%' OR userEmail LIKE '%{$_REQUEST['s']}%'
					OR actionedBy LIKE '%{$_REQUEST['s']}%' ".shgdprdm_pendingLikeType($_REQUEST['s']).shgdprdm_pendingLikeAdmin($_REQUEST['s'])." ) ";
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

		$result = shgdprdm_pendingPrettyPrint($result);
    return $result;
  }

  /**
 * Returns the count of records in the database.
 *
 * @return null|string
 */
  public static function shgdprdm_recordCount() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}shgdprdm_history
		WHERE (actionType = 5 OR actionType = 6)
		AND (actionVerify = 1 OR actionVerify = 5 OR actionVerify = 103)";

		// Add search condition if user is searching
		if(!empty( $_REQUEST['s'] )){
        $sql .= " and (actionID LIKE '%{$_REQUEST['s']}%' OR actionType LIKE '%{$_REQUEST['s']}%'
					OR userID LIKE '%{$_REQUEST['s']}%' OR userEmail LIKE '%{$_REQUEST['s']}%'
					OR actionedBy LIKE '%{$_REQUEST['s']}%' ".shgdprdm_pendingLikeType($_REQUEST['s']).shgdprdm_pendingLikeAdmin($_REQUEST['s'])." ) ";
    }
    return $wpdb->get_var( $sql );
  }

  /** Text displayed when no post data is available */
  public function no_items() {
    _e( 'No records avaliable.', 'sp' );
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

			$wrapStart = '';
			$wrapEnd = '';
			if( shgdprdm_mapLegacyRefs($item['actionVerify']) == 103 ||  $item['actionVerify'] == 'Admin Action Pending (Synced Record)'){
				$wrapStart="<span class='shgdprdm-admin-pending'>";
				$wrapEnd="</span>";
			}
      switch ( $column_name ) {
        case 'actionID':
        case 'actionType':
				case 'userID':
				case 'userEmail':
				case 'actionedBy':
				case 'actionTimestamp':
				case 'actionVerify':
				case 'randString':
          return $wrapStart.$item[ $column_name ].$wrapEnd;
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
			'actionedBy' => __( 'Admin Action' ),
			'actionTimestamp' => __( 'Timestamp' ),
			'actionVerify' => __( 'Verify Action' ),
			'randString' => __( 'Random String' )
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
		$total_items  = self::shgdprdm_recordCount();

		// Reset the pagination back to page-1 if searching
		if(!empty( $_REQUEST['s'] )){
			$current_page = 1;
		}

		$this->set_pagination_args( array(
      'total_items' => $total_items, // total number of items
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


	public function shgdprdm_pendingHistoryTable() { ?>
	<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
          <form method="post">
            <?php
						self::prepare_items();
            self::search_box('search', 'search_id');
            self::display();
            ?>
          </form>
        </div>
      </div>
    </div>
    <br class="clear">
  </div>
<?php }

} // end of class
} // end of if_exists
?>
