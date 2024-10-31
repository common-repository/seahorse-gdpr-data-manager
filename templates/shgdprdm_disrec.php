<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_recordsView($searchData){

// global $shgdprdm_error;


// $data = base64_decode($data['data']);
// $data = unserialize($data);

// $data = shgdprdm_extractReturnedSearchData($searchData);
// if( shgdprdm_validateExtractedSearchData($data) ){
// $rawData = $searchData['data'];
// $recordCount = count($data);
// $userRecordCount = count($data['userDetails']);
// }

if( shgdprdm_validateExtractedSearchData($searchData) ){
  $rawData = $searchData;
  $recordCount = count($searchData);
  $userRecordCount = count($searchData['userDetails']);
}
else{
  // exit();
  exit( wp_safe_redirect( admin_url( 'index.php?page=seahorse_gdpr_data_manager_plugin' ) ) );
}

// foreach($searchData as $a => $b){
//   echo "<br>".$a." => ".$b;
// }


?>

  <div>
    <h2 class="nav-tab-wrapper">
      <a href="?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager_records" class="nav-tab nav-tab-active">
        <span class="shgdprdm_icon-xl dashicons dashicons-list-view"></span>
        &nbsp;&nbsp;Records</a>
    </h2>
  </div>



  <div>
    <div id="shgdprdm-search-summary">
      <div id="shgdprdm-search-summary-details" class ="shgdprdm-notice info">
        <div>
          <h4>Searched User:</h4>
          <h2><?php echo esc_html($searchData['userDetails'][0]->Email);?></h2>
        </div>
        <div>
          <h4>Searched By: </h4>
          <h3><?php echo esc_html(shgdprdm_getSearchOption()[0]);?></h3>
        </div>
      </div>

      <div id="shgdprdm-search-summary-search-again">
        <div>
          <a href="admin.php?page=seahorse_gdpr_data_manager_plugin&tab=gdpr_data_manager" class="button button-primary">
          Search Again
          </a>
        </div>
      </div>
    </div>

    <?php
    if(shgdprdm_getUserNotice()){
      echo shgdprdm_getUserNotice();
    }
    ?>
  </div>



  <div id="shgdprdm-search-results-summary-details">
    <table class="wp-list-table widefat fixed striped pages">

    <?php
    foreach($searchData['userDetails'] as $col => $val){
      if($col == 0){ ?>
          <thead>
            <tr>
            <?php foreach($val as  $name => $contents){ ?>
                <th><?php echo esc_html( $name );?></th>
            <?php } ?>
            <?php if(count($searchData['userDetails']) > 1){ ?>
              <th></th>
            <?php } ?>
            <tr>
          </thead>
      <?php } ?>
      <tr>
      <?php foreach($val as  $name => $contents){ ?>
        <td><?php  echo esc_html( $contents );?></td>
      <?php } ?>
      <?php if(count($searchData['userDetails']) > 1){ ?>
        <td><?php echo esc_html( shgdprdm_searchSubmit($searchData['userDetails'][$col]->ID, 'none') );?></td>
      <?php } ?>
      </tr>
    <?php } ?>
    </table>
</div>



<?php
// Only Display if there are single user results
if(count($searchData['userDetails']) == 1){
  // Call class for generating user data
  $wsfParam = $searchData['userDetails'][0]->Email;
  try{
    $displayCl = new SHGdprdm_MSF($wsfParam);
    $output = $displayCl->shgdprdm_getDisplay();

    // $searchData['tableDetails'] = $output['tableDetails'];
  } catch (Exception $e) {
    die($e->getMessage());
    wp_safe_redirect( get_home_url() );
  }

  $tableCount = $output['tableDetails']['shgdprdm_tableCount'];
  $dataTableCount = $output['tableDetails']['shgdprdm_dataTableCount'];
  unset($output['tableDetails']['shgdprdm_tableCount']);
  unset($output['tableDetails']['shgdprdm_dataTableCount']);
  (isset($output['tableDetails']) && ( count($output['tableDetails']) > 0 ) ) ? $tDetails = true : $tDetails = false;

  // Display Records Summary Table
  echo shgdprdm_makeRecordsSummaryTable($tDetails,$tableCount,$dataTableCount);
  if($tDetails){

    // Display Records Details Table
    echo shgdprdm_makeRecordsDetailsTable($output['tableDetails']);

    if(
      function_exists( 'shgdprdm_getdisasterSyncValonSearch' ) &&
      function_exists( 'shgdprdm_getDisasterSyncRegDateCheck' ) &&
      function_exists( 'shgdprdm_makeRecordsActionButtons' )
    ){
      // Set the action buttons (update for Disaster Sync)
      // determine if sync record to display correct actions (show delete only if sync)
      $accButtonType = 1;
      $userNotice = FALSE;
      $isDisasterSync = shgdprdm_getdisasterSyncValonSearch( $searchData['userDetails'][0]->Email, 6 );

      // Check if this is a Disaster Sync Operation
      if( $isDisasterSync[0] == 1 ){
        // Check Registration Date of User
        $regD = shgdprdm_getDisasterSyncRegDateCheck($searchData['userDetails'][0]->Email);
        if($regD){
          if( $isDisasterSync[1] >=  $regD  ) {
            $accButtonType = 4;
          }
          else{
            $userNotice = TRUE;
          }
        }
        // Update for WooCommerce
        else{
          $accButtonType = 4;
        }
      }
      // Display Action Buttons
      echo shgdprdm_makeRecordsActionButtons(!empty($output['tableDetails']['posts'])?$output['tableDetails']['posts']:array(),$searchData['userDetails'][0], $accButtonType, $userNotice);
    }
    else{
      // Display Disabled Buttons
      echo shgdprdm_makeNoLicenceNoticeOpenTags();
      echo shgdprdm_makeNoLicenceNotice();
      echo shgdprdm_makeNoLicenceActionSection('sh_actionBtns',$searchData['userDetails'][0]);
      echo shgdprdm_makeNoLicenceNoticeCloseTags();
    }
  }
  ?>

  <?php
  } // End if Multiple Users


  ?>

<?php
update_option('shgdprdm_admin_msg','');
}
?>
