<?php
defined('SHGDPRDM_ABSPATH') or die('You are not authorised to access this file.');
function shgdprdm_searchSubmit($searchParam, $showHide = 'inline-block', $buttonLabel = "Fetch Data", $buttonID = "search", $buttonType = 'submit', $searchBy = 2, $placeholder = NULL, $buttonAttr = NULL ){
  $hidden = '';

  if($showHide != 'inline-block'){
    $hidden = 'style=\'display:none;\'';
  }
  $attr = array( 'id' => $buttonID);
  if( $buttonAttr ){
    $attr = array_merge($attr, $buttonAttr);
  }
  $html = "
  <form method=\"post\" action=\"".esc_url( admin_url( 'admin-post.php' ) )."?action=shgdprdm_search_action_hook\">
    <input type=\"hidden\" name=\"shgdprdmscon\" value=\"".$searchBy."\"/>
    <input type=\"hidden\" class=\"shgdprdm_search-field\" id=\"shgdprdmsparam\" name=\"shgdprdmsparam\" value=\"".$searchParam."\" placeholder = \"".$placeholder."\"/>
    <input type=\"hidden\"  id=\"action\" name=\"action\" value=\"shgdprdm_search_action_hook\"/>

    <div id=\"fetch-btn-container\">
    <input type=\"submit\" name=\"search\" id=\"search\" class=\"button button-primary\" value=\"".$buttonLabel."\"/>
    </div>
  </form>";

  return $html;
}

 ?>
