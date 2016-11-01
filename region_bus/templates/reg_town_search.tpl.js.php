<?php defined( 'ROOT_PATH' ) or die();?>
<script>
$(function(){
	   function displayResultRegBus(item) {
	   if(item.text!='Result not Found'){					
		var prefix='http://mybuses.ru/';
		var postfix='/way/';					
		var postfixxx='/';
		var town='<?=$town_alias;?>';
		var src=prefix+town+postfix+item.value+postfixxx;
		window.open(src, "_self");				
		}
		else{
		$('.alert-reg-bus').show().html('Увы ничего не найдено, попытайтесь снова');
		}
	}

	
	$('#bus_typeahead').typeahead({
		source: [
		<?
		................/*Тут в цикле перебираются все остановки до которых можно доехать из города $town_alias - конечные и промежуточные */
							 {id: '<?=$station_arrive_alias;?>', name: '<?=$station_arrive_name;?>'},
							 {id: '<?=$station_arrive_alias;?>', name: '<?=$station_arrive_name;?>'},
			..............................................................................				
		],
		onSelect: displayResultRegBus
	});
		
});
</script>