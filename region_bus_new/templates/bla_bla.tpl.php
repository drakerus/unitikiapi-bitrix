<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
<tr id="bla-bla-car-tr">
    <td><span class="lead"><?=$town_from?></span></td>
    <td>
        <ul class="list-inline">
            <?if(!empty($duration)){?><li><?=$duration;?></li><?}?>
            <li><small>попутчиком на автомобиле с опытным водителем</small></li>
            <li><?=$total?> <i class="glyphicon glyphicon-thumbs-up"></i></li>
        </ul>
    </td>
    <td><span class="lead"><?=$town_to?></span></td>
    <td><a href="https://www.blablacar.ru/search?fn=<?=$town_from?>&tn=<?=$town_to?>&comuto_cmkt=RU_MYBUSES_PSGR_OFFERS_none&utm_source=MYBUSES&utm_medium=API&utm_campaign=RU_MYBUSES_PSGR_OFFERS_none" title="<?=$town_from?> - <?=$town_to?> попутчиком на автомобиле" rel="nofollow" class="btn btn-warning btn-default bla-bla-car-link" role="button">На авто<br><?=$price?>р.</a></td>
</tr>