<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_pendingHistoryPage()
{ ?>

  <?php
  if(shgdprdm_getUserNotice()){
    echo shgdprdm_getUserNotice();
  }
  ?>

<div class="container">

  <div>
    <h2>Pending: </h2>
    <p><?php shgdprdm_pendingHistorySectionText();?></p>
  </div>


  <?php
  if(class_exists('SHGdprdm_PendingList')){
    shgdprdm_getPendingHistoryData();
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
