<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
<? if( empty($schedules) ){ ?>
    <div class="alert alert-danger text-center" role="alert"><h2>К сожалению рейсов <?=$way_name;?> не найдено.</h2><br/>
        <a href="/<?=$backlink?>/" class="btn btn-success" role="button">вернуться назад</a>
    </div>
<?} else { ?>
    <table class="table table-striped regtable">
        <thead>
        <tr>
            <th>
                <u>Отправление</u>
                <br/>
                <h2><a href="/<?=$info['from_town_alias']; ?>/" title="<?=htmlspecialchars($info['from_town']); ?> расписание автобусов"><span class="glyphicon glyphicon-flag"></span> <?=$info['from_town']; ?></a></h2>
                <?if (!empty($info['from_district'])) { ?>
                    <span class="hidden-xs"><?= $info['from_district']; ?></span>
                <?}?>
                <?if (!empty($info['from_region'])) { ?>
                    <span class="hidden-xs"><?= $info['from_region']; ?></span>
                <?}?>
                <?if (!empty($info['country_from']) && !empty($info['country_to']) && $info['country_to'] != $info['country_from']) {?>
                    <?=$info['country_from'] ?><br/>
                <?}?>
            </th>
            <th>
                <? if (sizeof($stations) > 0) { ?>
                    Автобус имеет остановки:
                    <ul class="list-unstyled">
                        <?foreach($stations as $town_name => $station){?>
                        <li>
                            <nobr>
                                <a href="/<?=$info['from_town_alias']?>/way/<?=$station['thread_alias'];?>/" title="Автобус <?=htmlspecialchars($station['thread_name']);?> расписание"><span class="glyphicon glyphicon-record"></span>
                                    <?=$town_name?>
                                </a>
                            </nobr>
                        </li>
                        <?}?>
                    </ul>
                <?}?>
                В пути
            </th>
            <th>
                <u>Прибытие</u><br/>
                <h2><span class="glyphicon glyphicon-flag"></span> <?=$info['to_town']; ?></h2>
                <?if (!empty($info['to_district'])) { ?><span
                    class="hidden-xs"><?=$info['to_district']; ?></span>
                <?}?>
                <?if (!empty($info['to_region'])) { ?><span
                    class="hidden-xs"><?= $info['to_region']; ?></span>
                <?}?>
                <?if (!empty($info['country_from']) && !empty($info['country_to']) && $info['country_to'] != $info['country_from']) { ?>
                    <?= $info['country_to'] ?><br/>
                <?}?>
            </th>
            <th></th>
        </tr>
        </thead>
        <tbody>
            <?foreach ($schedules as $schedule) {?>
            <tr data-shday="sh_<?=date('d_m_Y', strtotime($schedule['start_time']));?>">
                <td>
                    <ul class="list-inline">
                        <li>
                            <span class="lead"><?=date('H:i', strtotime($schedule['start_time']))?></span><br/>
                            <small><?=date('d', strtotime($schedule['start_time'])) . ' ' . $schedule['start_month'] . ' ' . date('Y', strtotime($schedule['start_time']))?></small>
                            <small><?=$schedule['start_day']?></small>
                        </li>
                        <li>
                            <small><?=$schedule['from_station']?></small>
                        </li>
                    </ul>
                </td>                
                <td><?=$schedule['travel_time']?></td>
                <td>
                    <ul class="list-inline">
                        <li>
                            <span class="lead"><?=date('H:i', strtotime($schedule['stop_time']))?></span><br/>
                            <small><?=date('d', strtotime($schedule['stop_time'])) . ' ' . $schedule['stop_month'] . ' ' . date('Y', strtotime($schedule['stop_time']))?></small>
                            <small><?=$schedule['stop_day']?></small>
                        </li>
                        <li>
                            <small><?=$schedule['to_station']?></small>
                        </li>
                    </ul>
               </td>
               <td>
                   <?foreach ($schedule['prices'] as $price) {?>
                        <a href="/mods/bus/unitiki_linker.php?data=<?=$schedule['link_data']?>"
                           class="btn btn-primary btn-default uniticket"
                           title="Купить билет <?=htmlspecialchars($schedule['thread_name'])?>"
                           rel="nofollow" role="button">Купить билет<br/><?=$price?></a>
                    <?}?>
               </td>
            </tr>
            <?}?>
        </tbody>
    </table>
<?}?>
