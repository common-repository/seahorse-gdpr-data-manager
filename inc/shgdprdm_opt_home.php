<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');


// Print the Heading
function shgdprdm_HomeInfoSectionText()
{
    echo '<h2>Your Database</h2>';
}

function shgdprdm_getHomeInfoTables($num = null)
{
    global $wpdb;

    $sql = "SHOW TABLES LIKE '%'";
    $results = $wpdb->get_results($sql);

    return $results;
}



function shgdprdm_getHomeInfoAllPIITablesArr()
{
    global $wpdb;
    
    $regList = shgdprdm_getAllIdColumnNames(true, true, true, true);
    $exList = shgdprdm_getExceptionColumnNames(true);
    $tableList = array_merge($regList, $exList);
      
    // create array of PII tables
    $PIITableArr = array();
    foreach ($tableList as $index => $value) {
        foreach ($value as $key => $coreTable) {
            if ($coreTable != null) {
                $PIITableArr[] = $wpdb->prefix . $key;
            }
        }
    }
        
    return $PIITableArr;
}



function shgdprdm_getHomeInfoAllPIIFieldArr()
{
    $regList = shgdprdm_getAllIdColumnNames(true, true, true, true);
    $exList = shgdprdm_getExceptionColumnNames(true);
    $tableList = array_merge($regList, $exList);
      
    // create array of PII tables
    $PIIFieldArr = array();
    foreach ($tableList as $index => $value) {
        foreach ($value as $key => $coreTable) {
            if ($coreTable != null) {
                $PIIFieldArr[] = $coreTable;
            }
        }
    }
        
    return $PIIFieldArr;
}


function shgdprdm_getHomeInfoTrafficLightArr($type)
{
    global $wpdb;
    
    /// 3 arrays
    /// [2] Active PII tables (true)
    /// [1] PII Tables ()
    /// [0] PII tables if WC/EDD inactive (false)
    
    $combi = shgdprdm_getHomeInfoPluginSupportCheckAdv();
    
    // expand type 1 to allow for standard installs that are running more than 1 supported plugin
    
    if ($type === '2') {
        $regList = shgdprdm_getAllIdColumnNames(true, true, $combi[0], $combi[1]);
        $exList = shgdprdm_getExceptionColumnNames(true);
        $tableList = array_merge($regList, $exList);
    } elseif ($type === '1') {
        $regList = shgdprdm_getAllIdColumnNames(true, true, false, false);
        $exList = shgdprdm_getExceptionColumnNames(false);
        $tableList = array_merge($regList, $exList);
    } elseif ($type === '0') { /// dont list any tables (basic)
        // $regList = shgdprdm_getAllIdColumnNames(false, false, false, false);
        // $exList = shgdprdm_getExceptionColumnNames(false);
        $tableList = array();
    }
      
    // create array of PII tables
    $TRArr = array();
    if (!empty($tableList)) {
        foreach ($tableList as $index => $value) {
            foreach ($value as $key => $coreTable) {
                if ($coreTable != null) {
                    $TRArr[] = $wpdb->prefix . $key;
                }
            }
        }
    }
    
        
    return $TRArr;
}



function shgdprdm_getHomeInfoTableSize($tableName)
{
    global $wpdb;
  
    $sql = "SELECT table_name AS 'name',
				round( ( data_length / 1024 / 1024 ), 2 ) 'Data',
				round( ( index_length / 1024 / 1024 ), 2 ) 'Index'
				FROM information_schema.TABLES
				WHERE table_schema = %s AND table_name = %s
				ORDER BY name ASC;";

    $results = $wpdb->get_results($wpdb->prepare($sql, DB_NAME, $tableName));
    return $results;
}

function shgdprdm_getHomeInfoTableCount($tableName)
{
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM $tableName";
    return $wpdb->get_var($sql);
}

function shgdprdm_getHomeInfoRowColour($tableName, $TRArr, $TRAllArr)
{
    $TRArr = unserialize($TRArr);
    $TRAllArr = unserialize($TRAllArr);
             
    $RowClass = '';
    if (in_array($tableName, $TRArr)) {
        $RowClass = 'style="background-color: #E0FDE0;"';
    } elseif (in_array($tableName, $TRAllArr)) {
        $RowClass = 'style="background-color: #FFF9EF;"';
    }
              
    return $RowClass;
}
        
        
        
        function shgdprdm_getHomeInfoTrafficLightOutput($tableName, $TRArr, $TRAllArr)
        {
            $TRArr = unserialize($TRArr);
            $TRAllArr = unserialize($TRAllArr);

            if (in_array($tableName, $TRArr)) {
                $TableOutput = '<span class="dashicons dashicons-yes" style="color:green"></span>';
            } elseif (in_array($tableName, $TRAllArr)) {
                $TableOutput = '<span class="dashicons dashicons-warning" style="color:orange"></span>';
            } else {
                $TableOutput = '<div><span class="dashicons dashicons-no" style="color:red"></span></div>';
            }
              
            return $TableOutput;
        }
        
        
function shgdprdm_getHomeInfoPluginSupportCheck()
{
    $validateControl = new SHGdprdm_ValidateControl();
    
    $active = '0';
     
    if (defined('SHGDPRDM_PRO')) {
        foreach (unserialize(SHGDPRDM_PRO) as $proSupport => $proOptions) {
            if ($validateControl->shgdprdm_validateVerifyLicence() && $validateControl->shgdprdm_validateHasLicence() && $validateControl->shgdprdm_validateIsProLicence($proSupport)) {
                $active = '2';
            }
        }
        
        if ($active == '0' && $validateControl->shgdprdm_validateVerifyLicence() && $validateControl->shgdprdm_validateHasLicence()) {
            $active = '1';
        }
    }

    return $active;
}


function shgdprdm_getHomeInfoPluginSupportCheckAdv()
{
    $combi = array();
    
    if (defined('SHGDPRDM_PRO')) {
        $validateControl = new SHGdprdm_ValidateControl();
        
        $counter = 0;
        
        foreach (unserialize(SHGDPRDM_PRO) as $proSupport => $proOptions) {
            $combi[$counter] = false;
            if (
              $validateControl->shgdprdm_validateVerifyLicence() &&
              $validateControl->shgdprdm_validateHasLicence() &&
              $validateControl->shgdprdm_validateIsProLicence($proSupport)
            ) {
                $combi[$counter] = true;
            }
            $counter++;
        }
    }
    /// if WC ONLY support set
    // if (isset(get_option('Woo-Commerce-Plugin')['Woo-Commerce-Plugin']) && (!isset(get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin']))) {
    //     $combi[0] = true;
    //     $combi[1] = false;
    // /// if EDD ONLY support set
    // } elseif (!isset(get_option('Woo-Commerce-Plugin')['Woo-Commerce-Plugin']) && (isset(get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin']))) {
    //     $combi[0] = false;
    //     $combi[1] = true;
    // /// if EDD & WC support set
    // } elseif (isset(get_option('Woo-Commerce-Plugin')['Woo-Commerce-Plugin']) && (isset(get_option('Easy-Digital-Downloads-Plugin')['Easy-Digital-Downloads-Plugin']))) {
    //     $combi[0] = true;
    //     $combi[1] = true;
    // } else {
    //     $combi[0] = false;
    //     $combi[1] = false;
    // }
    // exit(print_r($combi));
    return $combi;
}



function shgdprdm_getHomeInfoPrimaryKey($tableName, $CombinedArr)
{
    $CombinedArr = unserialize($CombinedArr);
    
    $PrimaryKey = '';
    foreach ($CombinedArr as $index => $value) {
        if ($tableName == $index) {
            $PrimaryKey = $value;
        }
    }
              
    if ($PrimaryKey == '') {
        $PrimaryKey = '--';
    }

    return $PrimaryKey;
}
