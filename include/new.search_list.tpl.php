<table class="table table-striped regtable">
    <thead>
    <tr>
        <th>
            <div class="row">
                <div class="col-xs-5 col-md-3">
                    <u>Отправление</u><br>
                    <h2><?= ( (mb_strlen($common['city_from'], 'UTF-8') <= 8) ? $common['city_from'] : mb_substr($common['city_from'], 0, 8, 'UTF-8').'<span class="hidden-xs">'.mb_substr($common['city_from'], 8, NULL, 'UTF-8').'</span>' )?></h2>
                    <span class="hidden-xs"><?=$common['country_from']?></span>
                </div>
                <div class="col-xs-2 col-md-3">	  В пути</div>
                <div class="col-xs-5 col-md-3">
                    <u>Прибытие</u><br>
                    <h2><?= ( (mb_strlen($common['city_to'], 'UTF-8') <= 8) ? $common['city_to'] : mb_substr($common['city_to'], 0, 8, 'UTF-8').'<span class="hidden-xs">'.mb_substr($common['city_to'], 8, NULL, 'UTF-8').'</span>' )?></h2>
                    <span class="hidden-xs"><?=$common['country_to']?></span>
                </div>
                <div class="col-xs-12 col-md-3"></div>
            </div>


            <div class="row">
                <div class="col-xs-12 col-md-12"  id="sandbox-container">
                    <div class="input-group date">
                        <span class="input-group-addon" id="sizing-addon_s">Выберите дату</span>
                        <input type="text" class="form-control" placeholder="Дата выезда" id="date_input" aria-describedby="sizing-addon_s">
                        <span class="input-group-addon" id="sizing-addon_s"><i class="glyphicon glyphicon-th"></i></span>
                    </div>
                </div>
            </div>
        </th>
    </tr>
    </thead>
    <tbody>
    <?foreach($data as $ride){?>
    <tr data-shday="sh_<?=date('d_m_Y', $ride['start'])?>">
        <td>
            <div class="row">
                <div class="col-xs-5 col-md-3">

                    <ul class="list-inline">
                        <li>
                            <i class="glyphicon glyphicon-time"></i>
                            <span class="lead"><?=date('H:i', $ride['start'])?></span>
                        </li><li>
                            <i class="glyphicon glyphicon-calendar"></i>
                            <small><?=date('d', $ride['start'])?> <?=_translateMonth(date('m', $ride['start']), true)?></small>
                            <small class="hidden-xs"><?=_translateDay(date('w', $ride['start']))?></small>
                        </li>
                        <li>
                            <i class="glyphicon glyphicon-map-marker"></i>
                            <small>Автостанция: <?=$ride['from']?></small>
                        </li>
                    </ul>
                </div>
                <div class="col-xs-2 col-md-3"><small><?=$ride['travel_time']?></small></div>
                <div class="col-xs-5 col-md-3">

                    <ul class="list-inline">
                        <li>
                            <i class="glyphicon glyphicon-time"></i>
                            <span class="lead"><?=date('H:i', $ride['end'])?></span><br>
                        </li>
                        <li>
                            <i class="glyphicon glyphicon-calendar"></i>
                            <small><?=date('d', $ride['end'])?> <?=_translateMonth(date('m', $ride['end']), true)?></small>
                            <small class="hidden-xs"><?=_translateDay(date('w', $ride['end']))?></small>
                        </li>
                        <li>
                            <i class="glyphicon glyphicon-map-marker"></i>
                            <small>Автостанция: <?=$ride['to']?></small>
                        </li>
                    </ul>

                </div>
                <div class="col-xs-12 col-md-3">
                    <!-- div class="alert alert-info col-xs-12" role="alert">Осталось <u></u> билетов</div -->
                    <?if($ride['place_cnt'] > 9){?>
                        <div class="alert alert-success col-xs-12" role="alert"><?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                    <?} elseif($ride['place_cnt'] > 5){?>
                        <div class="alert alert-info col-xs-12" role="alert"><?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                    <?}elseif($ride['place_cnt'] > 3){?>
                        <div class="alert alert-warning col-xs-12" role="alert"><?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                    <?}else{?>
                        <div class="alert alert alert-danger col-xs-12" role="alert"><strong>Внимание!</strong> <?=_timings($ride['place_cnt'], 'left')?> <u><?=$ride['place_cnt']?></u> <?=_timings($ride['place_cnt'], 'ticket')?></div>
                    <?}?>
                    <a href="/mods/bus/unitiki_linker.php?data=<?=$ride['link_data']?>" class="btn btn-primary  uniticket" title="Купить билет <?=$route_data['city_from']?> - <?=$route_data['city_to']?>" rel="nofollow" role="button" >Купить билет<br><?=$ride['price']?>р.</a>
                </div>
            </div>
        </td>
    </tr>
    <?}?>
    </tbody>
</table>