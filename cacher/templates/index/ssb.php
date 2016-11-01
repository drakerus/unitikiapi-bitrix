<script type="text/javascript">
$(function(){
	function displayResultBus(item){
		if(item.text!=='Result not Found'){
			var prefix='http://mybuses.ru/';
			var postfix='/bus/';
			var postfixxx='/';
			var town='<?=$town->town_alias?>';
			var src=prefix+town+postfix+item.value+postfixxx;window.open(src, "_self");
		}else{
			$('.alert-bus').show().html('Увы ничего не найдено, попытайтесь снова');
		}
	}
	$('#bus_typeahead').typeahead({
		source:<?=json_encode_unicode($buses_arrays)?>,
		onSelect: displayResultBus
	});
});
</script>