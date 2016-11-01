<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
<script>
$(function(){
    $('#sandbox-container .input-group-lg.date').datepicker({
        format: "dd.mm.yyyy",
        startDate: "<?=$start_date?>",
        endDate: "<?=$end_date?>",
        maxViewMode: 1,
        todayBtn: "linked",
        autoclose: true,
        language: "ru",
        todayHighlight: true,
        datesDisabled: <?=json_encode($dates_disabled)?>
    }).on('changeDate', function(e) {
        var day = e.date.getDate();
        day = day < 10 ? '0' + day : '' + day;
        var month = e.date.getMonth() + 1;
        month = month < 10 ? '0' + month : '' + month;
        var year = e.date.getFullYear();
        $('table.regtable tbody tr').hide();
        $('table.regtable tbody tr[data-shday="sh_' + day + '_' + month + '_' + year + '"]').show();
    });
    $('#date_input').change(function(){
        if ($(this).val() == '') $('table.regtable tr').show();
    });
});
</script>
