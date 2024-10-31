<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');

function shgdprdm_extRecordsView($data)
{
    global $shgdprdm_error;

    $rawData = $data;
    $data = base64_decode($data);
    $data = unserialize($data);
    $recordCount = count($data);
    $userRecordCount = count($data['userDetails']);

    $AccArr = array(1, 2, 3, 4, 5); ?>
    <div>
        <div><hr></div>
        <div id="shgdprdm-download-data-btn-group">
            <form id="shgdprdm_dl_group" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>?action=shgdprdm_export_action">
                <div id="shgdprdm-export-btn-group">
                    <input type="hidden" id="shgdprdm_uid" name ="shgdprdm_uid" value="<?php echo $data['userDetails'][0]->ID; ?>"/>
                    <input type="hidden" id="shgdprdm_user_email" name ="shgdprdm_user_email" value="<?php echo $data['userDetails'][0]->Email; ?>"/>
                    <input type="hidden" id="shgdprdm_expRd" name ="shgdprdm_expRd" value=<?php echo $rawData; ?>/>

                    <?php
                    $acVal = 1;
                    if (in_array($acVal, $AccArr)) {
                        $btnText = sprintf(
                            esc_html__(
                                'Download %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'XML'
                        );
                        $btnHoverText = sprintf(
                            esc_html__(
                                'Download Format: %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'XML'
                        );
                        $attr = array( 'id' => 'shgdprdm_export_xml', 'title' => $btnHoverText);
                        submit_button($btnText, 'primary', 'shgdprdm_export_xml', false, $attr);
                    }
                    
                    $acVal = 2;
                    if (in_array($acVal, $AccArr)) {
                        $btnText = sprintf(
                            esc_html__(
                                'Download %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'CSV'
                        );
                        $btnHoverText = sprintf(
                            esc_html__(
                                'Download Format: %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'CSV'
                        );
                        $attr = array( 'id' => 'shgdprdm_export_xml', 'title' => $btnHoverText);
                        submit_button($btnText, 'primary', 'shgdprdm_export_csv', false, $attr);
                    }
                    
                    $acVal = 3;
                    if (in_array($acVal, $AccArr)) {
                        $btnText = sprintf(
                            esc_html__(
                                'Download %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'JSON'
                        );
                        $btnHoverText = sprintf(
                            esc_html__(
                                'Download Format: %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'JSON'
                        );
                        $attr = array( 'id' => 'shgdprdm_export_json', 'title' => $btnHoverText);
                        submit_button($btnText, 'primary', 'shgdprdm_export_json', false, $attr);
                    }
                    
                    $acVal = 4;
                    if (in_array($acVal, $AccArr)) {
                        $btnText = esc_html__(
                            'Delete User',
                            'seahorse-gdpr-data-manager'
                        );
                        $btnHoverText = sprintf(
                            esc_html__(
                                'Download & Delete Data for ID %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            $detailsID
                        );
                        $attr = array( 'id' => 'shgdprdm_delete_user', 'title' => $btnHoverText);
                        submit_button($btnText, 'shgdprdm_delete_user', 'shgdprdm_delete_user', false, $attr);
                    }
                
                    $acVal = 5;
                    if (in_array($acVal, $AccArr)) {
                        $btnText = sprintf(
                            esc_html__(
                                'Download %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'XML'
                        );
                        $btnHoverText = sprintf(
                            esc_html__(
                                'Download Format: %s',
                                'seahorse-gdpr-data-manager'
                            ),
                            'XML'
                        );
                        $attr = array( 'id' => 'verify_delete', 'title' => 'Verify Data Deletion for ID '.$detailsID);
                        submit_button('Verify Delete', 'verify_delete', 'verify_delete', false, $attr);
                    }
                    ?>
                    
                    <p style="clear:both"></p>
                </div>
            </form>
        </div>

    <?php
}
