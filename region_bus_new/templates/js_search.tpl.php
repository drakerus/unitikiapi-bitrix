<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
$(function(){
function displayResultRegBus(item) {
    if(item.text!='Result not Found'){
        var prefix='http://mybuses.ru/';
        var postfix='/way/';
        var postfixxx='/';
        var town='<?=$town['town_alias'];?>';
        var src=prefix+town+postfix+item.value+postfixxx;
        window.open(src, "_self");
    }
    else{
        $('.alert-reg-bus').show().html('Увы ничего не найдено, попытайтесь снова');
    }
}

$('#reg_bus_typeahead').typeahead({
    source: [ <? foreach($stations as $idx => $station){ ?>
                {id: '<?=$station['search_alias'];?>', name: "<?=str_replace('"', "'", $station['search_name']);?>"}<? if( ($idx+1)<sizeof($stations) ){?>,<? }?>

    <?php }?>],
    onSelect: displayResultRegBus
});

});
