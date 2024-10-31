<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Display Records Helper Functions

function shgdprdm_makeRecordsSummaryTable($tableDetails,$tableCount,$dataTableCount){

  $table = '';
  $table .= '
    <div id="shgdprdm-search-results-summary-records">
      <table class="wp-list-table widefat fixed pages">
        <thead>
          <tr>';
  if($tableDetails){
    $table .= '
            <th>Tables in Your Database</th>
            <th>Tables with User Data</th>
            <th>Tables with User Data Records Found</th>';
  }
  $table .= '
            <th>Total Records Found</th>
            <th>Total Data Points Found</th>
          </tr>
        </thead>
        <tr>';
  if($tableDetails){
    $table .= '
          <td>'.esc_html($tableCount).'</td>
          <td>'.esc_html($dataTableCount).'</td>
          <td><span id="shgdprdm-total-tabledata-count-display"><img src="/wp-admin/images/loading.gif"/></span></td>';
  }
  $table .= '
          <td><span id="shgdprdm-total-record-count-display"><img src="/wp-admin/images/loading.gif"/></span></td>
          <td><span id="shgdprdm-total-data-count-display"><img src="/wp-admin/images/loading.gif"/></span></td>
        </tr>
      </table>
    <div><hr></div>
  </div>';

  return $table;
}

function shgdprdm_makeRecordsDetailsTable($data){

  $table = '';
  $table .= '
    <div id="shgdprdm-search-results-details-tbl">
      <table class="wp-list-table widefat fixed pages">
        <thead>
          <tr>
            <th style="text-align:left;">Table Name</th>
            <th>Has Data</th>
            <th>Data Records</th>
            <th>Data Points</th>
            <th></th>
          </tr>
        </thead>';
  $rowCount = 0;
  $totalRecords = 0;
  $totalDataPoints = 0;
  $tableDataRecord = 0;
  foreach($data as $tName => $tData){
    $rowCount % 2 ? $stripedClass = 'shgdprdm-even' : $stripedClass = 'shgdprdm-odd';
    $displayData = shgdprdm_displayDetails($tData);
    $totalRecords += (int)$displayData['rows'];
    $totalDataPoints += (int)$displayData['dataPoints'];
    $displayData['dataIcon'] = '<span class="dashicons dashicons-no" style="color:red"></span>';
    if(isset($displayData['rows'])){
    $displayData['dataIcon'] = '<span class="dashicons dashicons-yes" style="color:green"></span>';
    } else {
    $displayData['rows'] = 0;
    $displayData['dataPoints'] = 0;
    }
    /// remove count vars before display
    if($tName != 'shgdprdm_tableCount' && $tName != 'shgdprdm_dataTableCount') {
      $rowCount++;
      $showButton = '';
      if(isset($displayData['rows']) && $displayData['rows'] > 0){
        $tableDataRecord++;
        $showButton = '<div class="shgdprdm-view-data-record-btn button button-primary">View Data Records</div>';
      }
      // Dont escape $displayData['dataIcon'] OR $showButton as we define these above
      $table .= "<tr id='".esc_attr($tName)."' class='".$stripedClass."'>
                  <td style='text-align:left;'>".esc_html($tName)."</td>
                  <td>".$displayData['dataIcon']."</td>
                  <td>".esc_html($displayData['rows'])."</td>
                  <td>".esc_html($displayData['dataPoints'])."</td>
                  <td>".$showButton."</td>
                </tr>";
      if($displayData['rows'] > 0){
        $table .= "<tr style='text-align:left;' class='hidden ".$stripedClass."' id='".esc_attr($tName)."-data'><td colspan='5'><div class='shgdprdm-result-row'>".$displayData['display']."</div></td></tr>";
      }
    }
  }
  $table .=
  '</table>
  </div>
  <div id="shgdprdm-total-record-count" class="hidden">'.$totalRecords.'</div>
  <div id="shgdprdm-total-data-count" class="hidden">'.$totalDataPoints.'</div>
  <div id="shgdprdm-total-tabledata-count" class="hidden">'.$tableDataRecord.'</div>';

  return $table;
}
?>
