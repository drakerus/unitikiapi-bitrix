<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingregions">
        <h2><?=$station->name?></h2>

        <!-- ВЫБОР ДНЕЙ НЕДЕЛИ -->
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group btn-group-justified btn-group-sm" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <a href="./mobi_station_files/mobi_station.html" class="btn btn-default disabled" role="button">Выберите день</a>
                    </div>
                    <div class="btn-group" role="group">
                        <button class="btn btn-default" onclick="sh_select(&#39;noday&#39;)">
                            <i class="glyphicon glyphicon-eye-open"></i> Все дни
                        </button>
                    </div>
                </div>
            </div>
            <div id="sticky-wrapper" class="sticky-wrapper" style="height: 68px;">
                <div class="col-md-12 mobi_week_selector" style="width: 1138px;">
                    <div class="btn-group btn-group-justified btn-group-sm" role="group" aria-label="...">
                        <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select(&#39;mo&#39;)"> Пн</button></div>
                        <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select(&#39;tu&#39;)"> Вт</button></div>
                        <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select(&#39;we&#39;)"> Ср</button></div>
                        <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select(&#39;th&#39;)"> Чт</button></div>
                        <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select(&#39;fr&#39;)"> Пт</button></div>
                        <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select(&#39;sa&#39;)"> Сб</button></div>
                        <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select(&#39;su&#39;)"> Вс</button></div>
                    </div>

                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-th-list"></span> автобус:
                    </button>
                    <ul class="dropdown-menu">
                    <!-- ВЫПАДАЮЩИЙ СПИСОК ВСЕХ ОСТАНОВОК -->

                    <?php foreach ($buses as $bus): ?>
                        <li><a href="/<?=$town->town_alias?>/bus/<?=$bus->alias?>/" title="Расписание автобуса <?=$bus->name_escaped?>">Автобус №<?=$bus->bus_name?></a></li>
                    <?php endforeach; ?>
                        
                        <li>
                            <a href="./mobi_station_files/mobi_station.html" data-station="station_all">
                                <i class="glyphicon glyphicon-eye-open"></i> Все
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">

    <?php $i = 1; ?>
    <?php foreach ($buses as $bus_id => $bus): ?>

        <?php
            $direct = isset($buses_and_times[0][$bus_id]) ? $buses_and_times[0][$bus_id] : null;
            $return = isset($buses_and_times[1][$bus_id]) ? $buses_and_times[1][$bus_id] : null;

            if ($direct)
            {
                ksort($direct);
            }
            if ($return)
            {
                ksort($return);
            }

            list($begin_station, $end_station) = explode(' – ', $bus->f_way);
        ?>

        <!-- Первая остановка -->
        <div class="station-wrapper station<?=$i++?>">
            <!-- БЛОК ОСТАНОВКА -->
            <div class="row">
                <div id="sticky-wrapper" class="sticky-wrapper" style="height: 47px;">
                    <div class="col-sm-12 mobi_station_header" style="width: 1138px;">
                        <h3>
                            <a href="/<?=$town->town_alias?>/bus/<?=$bus->alias?>/" title="Расписание автобуса <?=$bus->name_escaped?>">
                                <?=$bus->bus_name?>
                            </a>
                        </h3>
                    </div>
                </div>
            </div>

            <?php if ($direct && $return): ?>

                <!-- БЛОК РЕЙСОВ -->
                <!-- ПРЯМЫЕ РЕЙСЫ -->
                <div class="row mobi_station_races">
                    <div class="col-md-12">
                        <div>
                             <button class="btn btn-default button-races1"><i class="glyphicon glyphicon-random"></i></button>
                             РЕЙСЫ к остановке "<?=$begin_station?>":
                        </div>

                        <?php foreach ($direct as $item): ?>
                            <span class="sh<?=$item->id_race_type?> mob_vis"><?=$item->time?></span>
                        <?php endforeach ?>
                     </div>
                </div>
                <!-- ОБРАТНЫЕ РЕЙСЫ -->
                <div class="row mobi_station_races">
                    <div class="col-md-12">
                        <div>
                            <button class="btn btn-default button-races2"><i class="glyphicon glyphicon-random"></i></button>
                            РЕЙСЫ к остановке "<?=$end_station?>":
                        </div>

                        <?php foreach ($return as $item): ?>
                            <span class="sh<?=$item->id_race_type?> mob_vis"><?=$item->time?></span>
                        <?php endforeach ?>
                    </div>
                </div>

            <?php elseif ($direct): ?>

                <div class="row mobi_station_races">
                    <div class="col-md-12">
                        <?php foreach ($direct as $item): ?>
                            <span class="sh<?=$item->id_race_type?> mob_vis"><?=$item->time?></span>
                        <?php endforeach ?>
                    </div>
                </div>

            <?php else: ?>

                <div class="row mobi_station_races">
                    <div class="col-md-12">
                        <?php foreach ($return as $item): ?>
                            <span class="sh<?=$item->id_race_type?> mob_vis"><?=$item->time?></span>
                        <?php endforeach ?>
                    </div>
                </div>

            <?php endif ?>
        </div>

    <?php endforeach ?>
    </div>

</div>
</div>
