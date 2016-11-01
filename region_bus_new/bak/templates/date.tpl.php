<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
<div class="btn-group" id="select-date">
    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Уточните дату отправления <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <? foreach($dates as $date){?>
            <li><a href="#" data-shday="sh_<?=date('d_m_Y', $date['date'])?>"><?=date('d', $date['date']).' '.$date['month'].' '.$date['day']?></a></li>
        <? } ?>
        <li role="separator" class="divider"></li>
        <li><a href="#">Любой день</a></li>
    </ul>
</div>