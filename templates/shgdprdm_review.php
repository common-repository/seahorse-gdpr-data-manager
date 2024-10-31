<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
// Page for reviewing the accounts that have had Export/delete process ran on them & by who

function shgdprdm_reviewHistoryPage()
{ ?>

  <?php
  // $table = New SP_Plugin;
  if(shgdprdm_getUserNotice()){
    echo shgdprdm_getUserNotice();
  }
  ?>

<div class="container">

  <div>
    <h2>Review Export History </h2>
    <p><?php shgdprdm_reviewHistorySectionText();?></p>
  </div>

      <?php

      // $refID = rand(0,999);
      // $actionID = 1;
      // $exportType = 1;
      // $userID = array(22,23,24);
      // $adminID = 3;
      // $adminTimestamp = date('Y-m-d H:i:s');
      // $userActionType = 2;
      // $userActionTimestamp = NULL;
      //
      // $updateData = array(
      //     'shgdprdm_rid' => $refID,
      //     'shgdprdm_awhat' => $actionID,
      //     'shgdprdm_uwho' => $userID,
      //     'shgdprdm_awho' => $adminID,
      //     'shgdprdm_awhen' => $adminTimestamp,
      //     'shgdprdm_uwhat' => $userActionType,
      //     'shgdprdm_uwhen' => $userActionTimestamp
      //   );
      if(class_exists('SHGdprdm_HistoryList')){
        $externalData = shgdprdm_getReviewHistoryExternalData();

        shgdprdm_getReviewHistoryData( $externalData['data'] );
      }
      else{
        echo shgdprdm_makeNoLicenceNoticeOpenTags();
        echo shgdprdm_makeNoLicenceNotice();
        echo shgdprdm_makeNoLicenceActionSection('sh_general');
        echo shgdprdm_makeNoLicenceNoticeCloseTags();
      }
      ?>

</div>
<?php } ?>
