<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
<tr id="bla-bla-car-tr-basic">
	<td><img class="blablalogo" src="/bitrix/templates/mybuses_schedule/images/blablacar_shift_wc_logo.png" width="243px" height="48px"></td>                
	<td>~  <?if(!empty($duration)){ echo $duration;} ?></td>
	<td><?=$total?> <br/><small>Поездка на машине с водителем, которому по пути</small></td>
	<td>	
	<a href="https://www.blablacar.ru/search?fn=<?=$town_from?>&tn=<?=$town_to?>&comuto_cmkt=RU_MYBUSES_PSGR_OFFERS_none&utm_source=MYBUSES&utm_medium=API&utm_campaign=RU_MYBUSES_PSGR_OFFERS_none" title="<?=$town_from?> - <?=$town_to?> попутчиком на автомобиле" rel="nofollow" class="btn btn-warning btn-default bla-bla-car-link" role="button">Цена<br>от <?=$price?>р.</a>
	</td>
</tr>