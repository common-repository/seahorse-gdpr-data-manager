// SCRIPTS FOR SEAHORSE "MY DATA VIEW" PLUGIN
jQuery.noConflict();
console.log('In User Scripts');

jQuery(document).ready(function($) {

	// $('input[id=shgdprdm_delete_user]').click(function(){
	// 	//$('input[id=delete_user]').attr("disabled", true);
	// 	$('input[id=shgdprdm_delete_user]').prop('disabled', true);
 //    $('input[id=shgdprdm_delete_user]').attr('disabled', true);
	// 	// $('#shgdprdm-user-action-notice').removeClass('hidden');
 //    // alert('clicked');
 //    $('#shgdprdm_dl_group').submit();
 // });


  $('input[id=shgdprdm_export_xml]').click(function(){
    $('input[id=shgdprdm_delete_user]').removeProp('disabled');
    $('input[id=shgdprdm_delete_user]').removeAttr('disabled');
    $('input[id=shgdprdm_delete_user]').addClass('shgdprdm_delete_user');
    $('#shgdprdm_download_first_notice').css({'display':'none'});
  });

  $('input[id=shgdprdm_export_csv]').click(function(){
    $('input[id=shgdprdm_delete_user]').removeProp('disabled');
    $('input[id=shgdprdm_delete_user]').removeAttr('disabled');
    $('input[id=shgdprdm_delete_user]').addClass('shgdprdm_delete_user');
    $('#shgdprdm_download_first_notice').css({'display':'none'});
  });

  $('input[id=shgdprdm_export_json]').click(function(){
    $('input[id=shgdprdm_delete_user]').removeProp('disabled');
    $('input[id=shgdprdm_delete_user]').removeAttr('disabled');
    $('input[id=shgdprdm_delete_user]').addClass('shgdprdm_delete_user');
    $('#shgdprdm_download_first_notice').css({'display':'none'});
  });

  // $('input[id=shgdprdm_delete_user]').click(function(){
  //   if($(this).attr('disabled') == 'disabled'){
  //     alert('button disabled');
  //   }
  //   else{
  //     alert('Button Clicked');
  //   }
  // });
});
