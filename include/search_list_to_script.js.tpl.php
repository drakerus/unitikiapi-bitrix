$(function(){
    function enableSearchButton(item){
        if(item.text!='Result not Found'){
            $("button.btn-primary").removeAttr("disabled");
            $("#town_to").parents("div.input-group-lg").removeClass("has-error").addClass("has-success");
            $("#town_to").attr('rel', item.value);
        }
        else
        {
            $("#town_to").parents("div.input-group-lg").removeClass("has-success").addClass("has-error");
            setTimeout(function () {
                $("#town_to").focus();
            }, 50);
            $("#town_to").attr('rel', '');
            $("button.btn-primary").attr("disabled", "disabled");
        }
    }
    $('#town_to').typeahead({
        source: [
        <? foreach($cities_to as $idx => $_city){ ?>
            {id: '<?=$_city->city_id?>', name: '<?=$_city->city_title?>'}<?=( ( ($idx + 1) == sizeof($cities_to) ) ? '' : ',' ).PHP_EOL?>
        <?}?>
        ],
        onSelect: enableSearchButton
    });
});