
jQuery(document).ready(function($) {
  
	$('tbody td li').toggleClass('columnIsVisible', true);
	
	$('.show-hide-column').click(function() {
		var targetTable = $(this).data('targettable');
		var columnsVisibility = [];
		var clickedBusNumber = $(this).data('busnumber');

		$('[class*="show-hide-column"][data-targettable="' + targetTable + '"]').each(function() {
			var busNumber = $(this).data('busnumber');
			var th = $('table#' + targetTable + ' th[class$="sh_th_' + busNumber + '"]');
			if (th.length > 0) {
				var visible = $(this).hasClass('active');
				if (busNumber == clickedBusNumber) visible = !visible;
				columnsVisibility.push([$(th).index() + 1, visible]);
			}
		});
		
		var visibleColumns = $.grep(columnsVisibility, function(value) { return value[1]; });
		if (visibleColumns.length == 0) {
			for (var i in columnsVisibility) columnsVisibility[i][1] = true;
		}
		
		updateColumnsVisibility(targetTable, columnsVisibility);
	});
	
	$('.show-all-columns').click(function() {
		var targetTable = $(this).data('targettable');
		var columnsVisibility = [];
		
		$('[class*="show-hide-column"][data-targettable="' + targetTable + '"]').each(function() {
			$(this).removeClass('active');
			var busNumber = $(this).data('busnumber');
			var th = $('table#' + targetTable + ' th[class$="sh_th_' + busNumber + '"]');
			if (th.length > 0) columnsVisibility.push([$(th).index() + 1, true]);
		});
		
		updateColumnsVisibility(targetTable, columnsVisibility);
	});
	
	function updateColumnsVisibility(targetTable, columnsVisibility) {
		for (var i in columnsVisibility) {
			$('#' + targetTable).find('thead th:nth-child(' + columnsVisibility[i][0].toString() + ')').toggle(columnsVisibility[i][1]);
			$('#' + targetTable).find('tbody td:nth-child(' + columnsVisibility[i][0].toString() + ')').toggle(columnsVisibility[i][1]);
			$('#' + targetTable).find('tbody td:nth-child(' + columnsVisibility[i][0].toString() + ') li').toggleClass('columnIsVisible', columnsVisibility[i][1]);
		}
	}
	
});