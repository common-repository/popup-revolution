jQuery(function ($) {

if ( pagenow == 'edit-wprp_popup' ) {
		
	$('.add-new-h2').addClass('button');
		
	
	$('#wpbody .wrap').wrapInner('<div id="wprp-col-left" />');
	$('#wpbody .wrap').wrapInner('<div id="wprp-cols" />');
	$('#wprp-col-right').removeClass('hidden').prependTo('#wprp-cols');
			
	$('#wprp-col-left > .icon32').insertBefore('#wprp-cols');
	$('#wprp-col-left > h2').insertBefore('#wprp-cols');
	   
    $('td.column-date').each(function(index, elem){

        var abbr = $(this).find('abbr').text();
       
        $(this).html(abbr);

    });

}

if ( pagenow == 'wprp_popup' ) {
	$('.add-new-h2').hide();
    
    $('#mask_colorpicker').hide();
    $('#mask_colorpicker').farbtastic('#mask_color_field');

    $('#mask_color_field').click(function() {
        $('#mask_colorpicker').fadeIn();
    });

    $('#border_colorpicker').hide();
    $('#border_colorpicker').farbtastic('#border_color_field');

    $('#border_color_field').click(function() {
        $('#border_colorpicker').fadeIn();
    });

    $(document).mousedown(function() {
            $('#mask_colorpicker').each(function() {
                var display = $(this).css('display');
                if ( display == 'block' )
                    $(this).fadeOut();
            });
            $('#border_colorpicker').each(function() {
                var display = $(this).css('display');
                if ( display == 'block' )
                    $(this).fadeOut();
            });
    });

}

$('#ibtn-enable').iButton({
    labelOn: "Yes",
    labelOff: "No"
});

$('#shortcode_text_input').click(function() {
	$(this).select();
});




//end
});