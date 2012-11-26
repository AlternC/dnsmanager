$(document).ready(function() {
    $("#form_sms_message").textlimit({
	counter: "#form_sms_message_counter",
	limit: 160
    });
});

$(document).ready(function() {
    $('.input-select-multiple').wrap('<div style="overflow: auto" />');
    $('.input-select-multiple').after('<a class="invert-selection" href="#">Inverser la sélection</a>');
    $('.invert-selection').click(function() {
	var all_cases = $(this).prev('.input-select-multiple').find('input:checkbox');
	var checked_cases = $(this).prev('.input-select-multiple').find('input:checked');
	all_cases.attr('checked','checked');
	checked_cases.removeAttr('checked');
	return false;
    });
});

$(document).ready(function() {
    $('form#servers_search #form_filter_fqdn').focus();
    $('form#servers_search select').change(function() {
	$(this).closest("form").submit();
    });
});


(function($) {
    $.fn.invertSelection = function(params) {
	params = $.extend( {button_selector: '.invert-selection'}, params);
	var cases_selector = $(this);
	var button_selector = params.button_selector;
	//$('.input-select-multiple').wrap('<div style="overflow: auto" />');
	//$('.input-select-multiple').after('<a class="invert-selection" href="#">Inverser la sélection</a>');
	$(button_selector).click(function() {
	    var all_cases = cases_selector.find('input:checkbox');
	    var checked_cases = cases_selector.find('input:checked');
	    all_cases.attr('checked','checked');
	    checked_cases.removeAttr('checked');
	    return false;
	});
	return this;
    };
})(jQuery);
