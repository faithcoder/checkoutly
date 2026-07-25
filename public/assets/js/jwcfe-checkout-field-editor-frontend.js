
jQuery(document).ready(function($) {
	var currRequest = null;

	// Frontend Chosen selects
	// if ( $().select2 ) {

	// 	$( 'select.checkout_chosen_select:not(.old_chosen), .form-row .select:not(.old_chosen)' ).filter( ':not(.enhanced)' ).each( function() {

	// 		$( this ).select2( {

	// 			minimumResultsForSearch: 10,
	// 			allowClear:  true,
	// 			placeholder: $( this ).data( 'placeholder' )
	// 		} ).addClass( 'enhanced' );
	// 	});
	// }

	$( '.checkout-date-picker' ).datepicker({
		numberOfMonths: 1,
		showButtonPanel: true,
		changeMonth: true,
      	changeYear: true,
		yearRange: "-100:+1"
	});

	$.fn.getType = function(){

		try{
			return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); 
		}catch(err) {
			return 'E001';
		}
	}
	
	function padZero(s, len, c){
		s = ""+s;
		var c = c || '0';
		while(s.length< len) s= c+ s;
		return s;
	}

	function isInt(value) {
	  	return !isNaN(value) && parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
	}

	function isInputField(field){
		if(field && field.length > 0){
			var tagName = field[0].tagName.toLowerCase();
			if($.inArray(tagName, ["input", "select", "textarea"]) > -1){
				return true;
			}
		}
		return false;
	}

	

	function getInputField(key){
		var field = null;
		if(key){
			field = $('#'+key);
			if(!isInputField(field)){
				field = $("input[name='"+key+"']");
				if(!isInputField(field)){
					field = $("input[name='"+key+"[]']");
					if(!isInputField(field)){
						field = $("input[name='"+key+"[0]']");
					}
				}
			}
		}
		return field;
	}

	 
});