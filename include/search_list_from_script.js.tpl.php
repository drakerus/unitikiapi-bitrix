/* mybuses.ru/search/js/search_list_from_script.js */
$(function(){
    function selectSearchToScript(item){
        if(item.text!='Result not Found')
        {
            $('#town_to').off();
            $('#town_to').data('typeahead', null);
            $('#town_to').val("");
            var url = "/search/js/" + item.value + ".search_list_to_script.js";
            $.getScript( url, function(data, textStatus, jqxhr) { });
            $("#town_from").parents("div.input-group-lg").removeClass("has-error").addClass("has-success");
            $("#town_from").attr('rel', item.value);
            setTimeout(function () {
                $("#town_to").removeAttr("disabled").focus();
            }, 50);
        }
        else
        {
                $("#town_from").attr('rel', '');
                $("#town_from").parents("div.input-group-lg").removeClass("has-success").addClass("has-error");
                setTimeout(function () {
                    $("#town_from").focus();
                }, 50);
                $("#town_to").val("");
                $("#town_to").attr("disabled","disabled");
                $("#search_btn").attr("disabled","disabled");
        }
    }
        $('#town_from').typeahead({
            source: [
            <? foreach($cities_from as $idx => $_city){ ?>
                {id: '<?=$_city->city_id?>', name: '<?=$_city->city_title?>'}<?=( ( ($idx + 1) == sizeof($cities_from) ) ? '' : ',' ).PHP_EOL?>
            <?}?>
            ],
        onSelect: selectSearchToScript
    });
});
