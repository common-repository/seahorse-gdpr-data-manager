<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_HomeInfoPage()
{
    if (shgdprdm_getUserNotice()) {
        echo shgdprdm_getUserNotice();
    } ?>
  <div class="container">

    <div>
    <?php shgdprdm_HomeInfoSectionText(); ?>
    </div>
    <div><hr></div>

    <div>
       <br/>
   <table class="wp-list-table widefat fixed striped pages">
    <thead>
    <th>DB Host</th>
    <th>DB Name</th>
    <th>DB User</th>
    <th>DB Pass</th>
    </thead>

      <tr>
        <td><?php echo DB_HOST; ?></td>
        <td><?php echo DB_NAME; ?></td>
        <td><?php echo DB_USER; ?></td>
        <td><?php echo str_repeat('*', strlen(DB_PASSWORD) - 3) . substr(DB_PASSWORD, -3); ?> <?php echo '<font color="red" size="1"> Chars: ('.strlen(DB_PASSWORD).')</font>'; ?></td>
      </tr>

       </table>
      <br/>
    <div><hr></div>

    <p><span class="dashicons dashicons-yes" style="color:green"></span> <?php echo SHGDPRDM_INDICATE_GREEN; ?></p>
    <p><span class="dashicons dashicons-warning" style="color:orange"></span> <?php echo SHGDPRDM_INDICATE_ORANGE; ?></p>
    <p><span class="dashicons dashicons-no" style="color:red"></span> <?php echo SHGDPRDM_INDICATE_RED; ?></p>

  <table class="wp-list-table widefat striped pages">
    <thead>
    <th></th>
    <th>Table</th>
    <th>Rows</th>
    <th>Size</th>
    <th>Primary Key</th>
    </thead>
  <?php

  /// all pii arrays
    $PIITableArr = shgdprdm_getHomeInfoAllPIITablesArr();
    $PIIFieldArr = shgdprdm_getHomeInfoAllPIIFieldArr();
    $CombinedArr = array_combine($PIITableArr, $PIIFieldArr);
    $TRAllArr = shgdprdm_getHomeInfoTrafficLightArr('2');
    $TRActiveArr = shgdprdm_getHomeInfoTrafficLightArr(shgdprdm_getHomeInfoPluginSupportCheck());


    $TotalRows = 0;
    $TotalDataSize = 0;
    $TotalIndexSize = 0;
    foreach (shgdprdm_getHomeInfoTables() as $index => $value) {
        foreach ($value as $tableName) {
            echo '<tr '.shgdprdm_getHomeInfoRowColour($tableName, serialize($TRActiveArr), serialize($TRAllArr)).'>';
            echo '<td>'.shgdprdm_getHomeInfoTrafficLightOutput($tableName, serialize($TRActiveArr), serialize($TRAllArr)).'</td>';
            echo '<td>'.$tableName.'</td>';
            echo '<td>'.shgdprdm_getHomeInfoTableCount($tableName).'</td>';
            echo '<td>';
            foreach (shgdprdm_getHomeInfoTableSize($tableName) as $index => $value) {
                foreach ($value as $key => $tableSize) {
                    if ($key != 'name') {
                        echo $key.' - '.$tableSize.'MB ';
                    }
                    if ($key == 'Data') {
                        $TotalDataSize += $tableSize;
                    } elseif ($key == 'Index') {
                        $TotalIndexSize += $tableSize;
                    }
                }
            }
            echo '</td>';
            echo '<td>';
            echo shgdprdm_getHomeInfoPrimaryKey($tableName, serialize($CombinedArr));
            echo '</td>';
            echo '</tr>';

            $TotalRows += shgdprdm_getHomeInfoTableCount($tableName);
        }
    }
    echo '<tr>
    <td></td>
    <td><b>Total Tables:</b> '.count(shgdprdm_getHomeInfoTables()).'</td>
    <td><b>Total Rows:</b> '.$TotalRows.'</td>
    <td><b>Total Size:</b> Data '.$TotalDataSize.'MB | Index '.$TotalIndexSize.'MB</td>
    <td></td>
    </tr>'; ?>
      </table>
    </div>


  </div>

<?php
} ?>
