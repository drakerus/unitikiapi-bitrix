<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
<? if( empty($threads) ){ ?>
    <div class="alert alert-danger text-center" role="alert"><h2>К сожалению рейсов <?=$way_name;?> не найдено.</h2><br/>
        <a href="/<?=$backlink?>/" class="btn btn-success" role="button">вернуться назад</a>
    </div>
<?} else { ?>
    <? foreach ($threads as $thread) { ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th><u>Отправление</u><br/>

                    <h3><a href="/<?= $thread['from_town_alias']; ?>/terminus/<?= $thread['from_station_alias']; ?>/"
                           title="Расписание автобусов автостанция <?= $thread['from_station']; ?>"><?= $thread['from_station']; ?></a>
                    </h3>
                    <h4><a href="/<?= $thread['from_town_alias']; ?>/"><?= $thread['from_town']; ?></a></h4>
                    <?if (!empty($thread['from_district'])) { ?><span
                        class="hidden-xs"><?= $thread['from_district']; ?></span><?
                    } ?>
                    <?if (!empty($thread['from_region'])) { ?><span
                        class="hidden-xs"><?= $thread['from_region']; ?></span><?
                    } ?>
                    <?if (!empty($thread['country_from']) && !empty($thread['country_to']) && $thread['country_to'] != $thread['country_from']) { ?>
                        <?= $thread['country_from'] ?><br/>
                    <?
                    } ?>
                </th>
                <th>В пути</th>
                <th><u>Прибытие</u><br/>
                    <? /*	<h3><a href="/<?=$header_array['town_alias_to'];?>/terminus/<?=$header_array['station_alias_to'];?>/" title="Расписание автобусов автостанция <?=$header_array['station_from'];?>"><?=$header_array['station_to'];?></a></h3> */ ?>
                    <h3><?= $thread['to_station']; ?></h3>
                    <h4>
                        <?if (!empty($thread['to_town_alias'])) { ?>
                            <a href="/<?= $thread['to_town_alias']; ?>/"
                               title="Расписание автобусов <?= $thread['to_town']; ?>"><?= $thread['to_town']; ?></a>
                        <?
                        } else {
                            ?>
                            <?= $thread['to_town']; ?>
                        <?
                        } ?>
                    </h4>
                    <?if (!empty($thread['to_district'])) { ?><span
                        class="hidden-xs"><?= $thread['to_district']; ?></span><?
                    } ?>
                    <?if (!empty($thread['to_region'])) { ?><span
                        class="hidden-xs"><?= $thread['to_region']; ?></span><?
                    } ?>
                    <?if (!empty($thread['country_from']) && !empty($thread['country_to']) && $thread['country_to'] != $thread['country_from']) { ?>
                        <?= $thread['country_to'] ?><br/>
                    <?
                    } ?>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?
            foreach ($thread['schedules'] as $schedule) {
                foreach ($schedule as $start_time => $time_data) {?>
                    <tr>
                        <td><span class="lead"><?= date('H:i', strtotime($start_time)); ?></span><br/>
                            <small><?= date('d', strtotime($start_time)) . ' ' . $time_data['start_month'] . ' ' . date('Y', strtotime($start_time)); ?></small>
                        </td>
                        <td><?= $thread['travel_time']; ?></td>
                        <td><span class="lead"><?= date('H:i', strtotime($time_data['stop_month'])); ?></span><br/>
                            <small><?= date('d', strtotime($time_data['stop_time'])) . ' ' . $time_data['stop_month'] . ' ' . date('Y', strtotime($time_data['stop_time'])); ?></small>
                        </td>
                        <td>
                            <?foreach ($thread['prices'] as $price) {
                                ?>
                                <a href="/mods/bus/unitiki_linker.php?data=<?= $time_data['link_data'] ?>"
                                   class="btn btn-primary btn-default"
                                   title="Купить билет <?= $thread['thread_name']; ?>"
                                   rel="nofollow" role="button">Купить билет<br/><?= $price; ?></a>
                            <?
                            } ?>
                        </td>
                    </tr>
                <?
                }
            } ?>
            </tbody>
        </table>
    <?
    }
}?>