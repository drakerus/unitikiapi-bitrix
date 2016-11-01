jQuery(document).ready(function($) {
	$('div#select-date ul.dropdown-menu li a').click(function (e) {
	    var shday = $(this).attr('data-shday'); 
	    $('table.regtable tbody tr').show();
	    if (shday) {
	    	$('table.regtable tbody tr[data-shday!="' + shday + '"]').hide();
	    }
	    e.preventDefault();
	});
});