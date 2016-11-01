<script type="text/javascript">
$(function(){
	function displayResultStation(item) {
		if(item.text!=='Result not Found'){
			var prefix='http://mybuses.ru/';
			var postfix='/station/';			
			var postfixxx='/';
			var town='<?=$town->town_alias;?>';
			var src=prefix+town+postfix+item.value+postfixxx;
			window.open(src, "_self");					
		}else{
			$('.alert-station').show().html('Увы ничего не найдено, попытайтесь снова');
		}
	}
	$('#station_typehead').typeahead({
		source: <?=json_encode_unicode($stations_arrays)?>,
		onSelect: displayResultStation
	});
});
</script>