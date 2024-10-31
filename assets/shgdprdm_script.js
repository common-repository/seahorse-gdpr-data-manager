// SCRIPTS FOR SEAHORSE "GDPR DATA MANAGER" PLUGIN
jQuery.noConflict();

// jQuery(document).ready(function($) {
//   wp.codeEditor.initialize($('#shgdprdm_tandc_option_input'), cm_settings);
// })
function shgdprdmDisableSearch(){
	var searchInputs = jQuery('#shgdprdm-main-search-fields-container').find('input');
	
	jQuery.each(searchInputs, function(iix, input){
		console.log('searchInputs: '+input);
		if( jQuery(input).attr('disabled') ){
			jQuery(input).removeAttr('disabled');
			jQuery(input).removeProp('disabled');
		}
		else{
			jQuery(input).attr('disabled','disabled');
			jQuery(input).prop('disabled','disabled');
		}
	});
}


jQuery(document).ready(function($) {

	var shgdprdmEditButtons = ['edit-shgdprdm-search-by-options-inline-btn','edit-shgdprdm-extra-search-options-inline-btn','edit-shgdprdm-text-option-btn'];
	var shgdprdmEditButtonCallbacks = [shgdprdmShowHideSearchBy,shgdprdmShowHideAlsoSearched,shgdprdmShowHideReplacementText];
	function shgdprdmDisableEditButtons(currentEditButton){
		var btnElem;
		$.each(shgdprdmEditButtons, function(iix, btnName){
			if(btnName != currentEditButton){
				btnElem = $('#'+btnName);
				if( btnElem.attr('disabled') ){
					btnElem.removeAttr('disabled');
					btnElem.removeProp('disabled');
					console.log('Callback @ '+iix+': '+shgdprdmEditButtonCallbacks[iix]);
					btnElem.on('click',shgdprdmEditButtonCallbacks[iix]);
				}
				else{
					btnElem.attr('disabled','disabled');
					btnElem.prop('disabled','disabled');
					btnElem.off('click');
				}
			}
		});
	}

	function shgdprdmShowHideSearchBy(){
		console.log('In show hide search by');
		// Disable All Other Buttons
		shgdprdmDisableEditButtons('edit-shgdprdm-search-by-options-inline-btn');
	    // Show the Options Fields
	    $('#shgdprdm-search-by-options-inline-container').toggle();
	    // Disable Search Field
	    shgdprdmDisableSearch();
	    // Change the Button Display
	    $(this).text( $(this).text() == "Click to change \"Search-By\"" ? "Cancel" : "Click to change \"Search-By\"" );
	    $(this).toggleClass('button-primary');
	    $(this).toggleClass('button-danger');
	}
	
	function shgdprdmShowHideAlsoSearched(){
		// Disable All Other Buttons
		shgdprdmDisableEditButtons('edit-shgdprdm-extra-search-options-inline-btn');
	    // Show the Options Fields
	    $('#shgdprdm-search-extra-container').toggle();
	    $('#shgdprdm-search-extra-selected-container').toggle();
	    // Disable Search Field
	    shgdprdmDisableSearch();
	    // Change the Button Display
	    $(this).text( $(this).text() == "Click to Edit" ? "Cancel" : "Click to Edit");
	    $(this).toggleClass('button-primary');
	    $(this).toggleClass('button-danger');
	}
	
	function shgdprdmShowHideReplacementText(){
		// Disable All Other Buttons
		shgdprdmDisableEditButtons('edit-shgdprdm-text-option-btn');
		// Activate the Options Fields
		var textBox = $('#shgdprdm_text_option_input');
		if( textBox.attr('disabled') ){
			textBox.removeAttr('disabled');
			textBox.removeProp('disabled');
		}
		else{
			textBox.attr('disabled','disabled');
			textBox.prop('disabled','disabled');
		}
	    // Show the Save Settings Button
	    $('#edit-shgdprdm-text-submit').toggle();
	    // Disable Search
	    shgdprdmDisableSearch();
	    // Change Button Display
	    $(this).text( $(this).text() == "Click to Edit" ? "Cancel" : "Click to Edit");
	    $(this).toggleClass('button-primary');
	    $(this).toggleClass('button-danger');
	}

	
	// Populate totals in on Search Result Page
	if( $( "#shgdprdm-total-record-count" ).length && $( "#shgdprdm-total-record-count-display" ).length ) {
		if( $( "#shgdprdm-total-record-count" ).text() ){
			$( "#shgdprdm-total-record-count-display" ).find( 'img' ).remove();
			$( "#shgdprdm-total-record-count-display" ).text( $( "#shgdprdm-total-record-count" ).text() );
		}
	}
	if( $( "#shgdprdm-total-data-count" ).length && $( "#shgdprdm-total-data-count-display" ).length ) {
		if( $( "#shgdprdm-total-data-count" ).text() ){
			$( "#shgdprdm-total-data-count-display" ).find( 'img' ).remove();
			$( "#shgdprdm-total-data-count-display" ).text( $( "#shgdprdm-total-data-count" ).text() );
		}
	}
	if( $( "#shgdprdm-total-tabledata-count" ).length && $( "#shgdprdm-total-tabledata-count-display" ).length ) {
		if( $( "#shgdprdm-total-tabledata-count" ).text() ){
			$( "#shgdprdm-total-tabledata-count-display" ).find( 'img' ).remove();
			$( "#shgdprdm-total-tabledata-count-display" ).text( $( "#shgdprdm-total-tabledata-count" ).text() );
		}
	}
	
	// Addition for in-line editing of Additional Searches June 2019
	
	// Show "search By" options when "Edit" button is clicked.
	$('#edit-shgdprdm-search-by-options-inline-btn').click(shgdprdmShowHideSearchBy);
	// $('#edit-shgdprdm-search-by-options-inline-btn').click(function(){
	// 	// Disable All Other Buttons
	// 	shgdprdmDisableEditButtons('edit-shgdprdm-search-by-options-inline-btn');
 //   // Show the Options Fields
 //   $('#shgdprdm-search-by-options-inline-container').toggle();
 //   // Disable Search Field
 //   shgdprdmDisableSearch();
 //   // Change the Button Display
 //   $(this).text( $(this).text() == "Click to change \"Search-By\"" ? "Cancel" : "Click to change \"Search-By\"" );
 //   $(this).toggleClass('button-primary');
 //   $(this).toggleClass('button-danger');
 // });
  
  // Show "Also Searched" options when "Edit" button is clicked.
  $('#edit-shgdprdm-extra-search-options-inline-btn').click(shgdprdmShowHideAlsoSearched);
  // $('#edit-shgdprdm-extra-search-options-inline-btn').click(function(){
  //   // Disable All Other Buttons
		// shgdprdmDisableEditButtons('edit-shgdprdm-extra-search-options-inline-btn');
  //   // Show the Options Fields
  //   $('#shgdprdm-search-extra-container').toggle();
  //   $('#shgdprdm-search-extra-selected-container').toggle();
  //   // Disable Search Field
  //   shgdprdmDisableSearch();
  //   // Change the Button Display
  //   $(this).text( $(this).text() == "Click to Edit" ? "Cancel" : "Click to Edit");
  //   $(this).toggleClass('button-primary');
  //   $(this).toggleClass('button-danger');
  // });
	
	// Enable Text field when "Edit" button is clicked
	$('#edit-shgdprdm-text-option-btn').click(shgdprdmShowHideReplacementText);
	// $('#edit-shgdprdm-text-option-btn').click(function(){
	// 	// Disable All Other Buttons
	// 	shgdprdmDisableEditButtons('edit-shgdprdm-text-option-btn');
	// 	// Activate the Options Fields
	// 	var textBox = $('#shgdprdm_text_option_input');
	// 	if( textBox.attr('disabled') ){
	// 		textBox.removeAttr('disabled');
	// 		textBox.removeProp('disabled');
	// 	}
	// 	else{
	// 		textBox.attr('disabled','disabled');
	// 		textBox.prop('disabled','disabled');
	// 	}
 //   // Show the Save Settings Button
 //   $('#edit-shgdprdm-text-submit').toggle();
 //   // Disable Search
 //   shgdprdmDisableSearch();
 //   // Change Button Display
 //   $(this).text( $(this).text() == "Click to Edit" ? "Cancel" : "Click to Edit");
 //   $(this).toggleClass('button-primary');
 //   $(this).toggleClass('button-danger');
 // });

	// Update checked & value of Input when checkbox ticked
	$( '#shgdprdm-search-extra-container input[type="checkbox"]' ).each( function() {
		$(this).click(function(){
			if($(this).is(':checked')){
				$(this).attr('checked','checked');
				$(this).attr('value','checked');
			}
			else{
				$(this).attr('checked',false);
				$(this).attr('value','unchecked');
			}
	 })
 });
	// Toggle the Select All Option
	$( '#shgdprdm-search-extra-container #shgdprdm-search-extra-select-all' ).click( function () {
    $( '#shgdprdm-search-extra-container input[type="checkbox"]' ).attr('checked', this.checked);
		$( '#shgdprdm-search-extra-container input[type="checkbox"]' ).attr('value', this.value);
		});

	// If WooCommerce guest search is selected, then also select WooCommerce general search
	$( '#shgdprdm-search-extra-container input[id="shgdprdm_plugins_settings[Woo-Commerce-Guest-Accounts]" ' ).click( function () {
		var parent = $( '#shgdprdm-search-extra-container input[id="shgdprdm_plugins_settings[Woo-Commerce-Plugin]" ' );
		if($(this).is(":checked") && !parent.is(":checked")){
			parent.attr('checked','checked');
			parent.attr('value','checked');
		}
  });
	// If WooCommerce general search Is De-selected, then guest search is also de-selected
	$( '#shgdprdm-search-extra-container input[id="shgdprdm_plugins_settings[Woo-Commerce-Plugin]" ' ).click( function () {
		var child = $( '#shgdprdm-search-extra-container input[id="shgdprdm_plugins_settings[Woo-Commerce-Guest-Accounts]" ' );
		if(!$(this).is(":checked")){
			if(child.is(":checked")){
				child.click();
			}
		}
  });

	$('.shgdprdm-view-data-record-btn').each( function() {
		$(this).click(function(){
			$('#'+$(this).parent().closest('tr').attr('id')+'-data').toggleClass('hidden');
			$(this).text(function(i, text){
          return text === "View Data Records" ? "Hide Data Records" : "View Data Records";
      })
	 })
 });

 $('.shgdprdm-view-screen-shot-btn').each( function() {
	$(this).click(function(){
		$('#'+$(this).parent().closest('div').attr('id')+'-data').toggleClass('hidden');
		$(this).text(function(i, text){
				 return text === "View Screen Shot" ? "Hide Screen Shot" : "View Screen Shot";
		 })
 	})
 });

});
