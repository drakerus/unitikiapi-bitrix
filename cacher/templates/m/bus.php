<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingregions">
            <h2>АВТОБУС №<?=$bus->bus_name?></h2>

            <!-- ВЫБОР ДНЕЙ НЕДЕЛИ -->
            <div class="row">
                <div class="col-md-12">
                    <div class="btn-group btn-group-justified btn-group-sm" role="group" aria-label="...">
                        <div class="btn-group" role="group">
                            <a href="#" class="btn btn-default disabled" role="button">Выберите день</a>
                        </div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-default" onclick="sh_select('noday')"><i class="glyphicon glyphicon-eye-open"></i> Все дни</button>
                        </div>
                    </div>
                </div>

                <div id="sticky-wrapper" class="sticky-wrapper" style="height: 68px;">
                    <div class="col-md-12 mobi_week_selector" style="width: 1138px;">
                        <div class="btn-group btn-group-justified btn-group-sm" role="group" aria-label="...">
                            <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select('mo')"> Пн</button></div>
                            <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select('tu')"> Вт</button></div>
                            <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select('we')"> Ср</button></div>
                            <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select('th')"> Чт</button></div>
                            <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select('fr')"> Пт</button></div>
                            <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select('sa')"> Сб</button></div>
                            <div class="btn-group" role="group"><button class="btn btn-default" onclick="sh_select('su')"> Вс</button></div>
                        </div>

                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="glyphicon glyphicon-th-list"></span> остановка:
                        </button>
                        <ul class="dropdown-menu">
                        <!-- ВЫПАДАЮЩИЙ СПИСОК ВСЕХ ОСТАНОВОК -->

                        <?php $i = 1; ?>
                        <?php foreach ($stations as $station_id): ?>
                            <li><a href="#" data-station="station<?=$i++?>"><?=$stations_by_id[$station_id]->name?></a></li>
                        <?php endforeach; ?>
                            <li><a href="#" data-station="station_all"><i class="glyphicon glyphicon-eye-open"></i> Все</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-body">
        <?php $i = 1; ?>
        <?php foreach ($stations as $station_id): ?>

            <?php
                $station = $stations_by_id[$station_id];
                $direct = isset($stations_and_times[0][$station_id]) ? $stations_and_times[0][$station_id] : null;
                $return = isset($stations_and_times[1][$station_id]) ? $stations_and_times[1][$station_id] : null;

                if ($direct)
                {
                    ksort($direct);
                }
                if ($return)
                {
                    ksort($return);
                }
            ?>

            
            <!-- Остановка -->
            <div class="station-wrapper station<?=$i++?>">
                <!-- БЛОК ОСТАНОВКА -->
                <div class="row">
                    <div id="sticky-wrapper" class="sticky-wrapper" style="height: 47px;">
                        <div class="col-sm-12 mobi_station_header" style="width: 1138px;">
                            <h3>
                                <a href="/<?=$town->town_alias?>/station/<?=$station->alias?>/" title="Расписание автобусов на остановке <?=$station->name_escaped?>"><?=$station->name?></a>
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
            
        <?php endforeach; ?>
	</div>
    </div>
</div>
