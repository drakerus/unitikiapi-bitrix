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
		$('.alert-reg-bus').show().html('��� ������ �� �������, ����������� �����');
		}
	}

	
	$('#bus_typeahead').typeahead({
		source: [
		<?
		................/*��� � ����� ������������ ��� ��������� �� ������� ����� ������� �� ������ $town_alias - �������� � ������������� */
							 {id: '<?=$station_arrive_alias;?>', name: '<?=$station_arrive_name;?>'},
							 {id: '<?=$station_arrive_alias;?>', name: '<?=$station_arrive_name;?>'},
			..............................................................................				
		],
		onSelect: displayResultRegBus
	});
		
});
</script>