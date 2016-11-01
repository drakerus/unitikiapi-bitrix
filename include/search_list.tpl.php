<div class="row">
    <div class="col-md-12" id="sandbox-container">
        <div class="input-group input-group-lg date">
            <span class="input-group-addon" id="sizing-addon_s">Выберите дату</span>
            <input type="text" class="form-control" placeholder="Дата выезда" id="date_input" aria-describedby="sizing-addon_s">
            <span class="input-group-addon" id="sizing-addon_s"><i class="glyphicon glyphicon-th"></i></span>
        </div>
    </div>
</div>
<?foreach($routes as $route_id => $route_data){?>
<table class="table table-striped regtable">
    <thead>
    <tr>
        <th><u>Отправление</u><br/>
            <h2><span class="glyphicon glyphicon-flag"></span> <?=$route_data['city_from']?></h2>
            <h3><?=$route_data['region_from']?></h3>
            <span class="hidden-xs"><?=$route_data['district_from']?></span>
            <br/><span class="hidden-xs"><?=$route_data['country_from']?></span>

        </th>
        <th>
            В пути</th>
        <th><u>Прибытие</u><br/>
            <h2><span class="glyphicon glyphicon-flag"></span> <?=$route_data['city_to']?></h2>
            <h3><?=$route_data['region_to']?></h3>
            <span class="hidden-xs"><?=$route_data['district_to']?></span>
            <br/><span class="hidden-xs"><?=$route_data['country_to']?></span>
        </th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?foreach($data[$route_id] as $ride){?>
        <tr data-shday="sh_<?=date('d_m_Y', $ride['start'])?>">
             <td>
                 <ul class="list-inline">
                     <li>
                         <span class="lead"><?=date('H:i', $ride['start'])?></span><br/>
                         <small><?=date('d', $ride['start'])?> <?=_translateMonth(date('m', $ride['start']))?> <?=date('Y', $ride['start'])?></small>
                         <small><?=_translateDay(date('w', $ride['start']))?></small>
                     </li>
                     <li>
                         <small><?=$route_data['city_from']?></small><br/>
                         <small>(<?=$ride['from']?>)</small>
                     </li>
                 </ul>
             </td>
            <td><?=_getTravelTime( $ride['end'] - $ride['start'] )?></td>
            <td>
                <ul class="list-inline">
                    <li>
                        <span class="lead"><?=date('H:i', $ride['end'])?></span><br/>
                        <small><?=date('d', $ride['end'])?> <?=_translateMonth(date('m', $ride['end']))?> <?=date('Y', $ride['end'])?></small>
                        <small><?=_translateDay(date('w', $ride['end']))?></small>
                    </li>
                    <li>
                        <small><?=$route_data['city_to']?></small><br/>
                        <small>(<?=$ride['to']?>)</small>
                    </li>
                </ul>

            </td>
            <td>
                <ul class="list-inline">
                    <li>

                        <a href="/mods/bus/unitiki_linker.php?data=<?=$ride['link_data']?>"
                           class="btn btn-primary btn-lg uniticket"
                           title="Купить билет <?=$route_data['city_from']?> - <?=$route_data['city_to']?>"
                           rel="nofollow" role="button">Купить билет<br/><?=$ride['price']?>р.</a>

                    </li>
                    <li>
                        <?if($ride['is_relevant'] == 0){?>
                        <span class="label label-info"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> данные рейса могли устареть</span>
                        <?}?>
                        <?if($ride['place_cnt'] > 9){?>
                            <div class="alert alert-success" role="alert"><?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                        <?} elseif($ride['place_cnt'] > 5){?>
                            <div class="alert alert-info" role="alert"><?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                        <?}elseif($ride['place_cnt'] > 3){?>
                            <div class="alert alert-warning" role="alert"><?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                        <?}else{?>
                            <div class="alert alert alert-danger" role="alert"><strong>Внимание!</strong> <?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                        <?}?>
                        </div>
                    </li></ul>

            </td>
        </tr>
    <?}?>
    </tbody>
</table>
<?}?>