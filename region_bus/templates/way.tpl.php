<?php defined( 'ROOT_PATH' ) or die();setlocale(LC_TIME, 'ru_RU');?>
<? if( empty($schedules) ){ ?>
    <div class="alert alert-danger text-center" role="alert"><h2>К сожалению рейсов <?=$way_name;?> не найдено.</h2><br/>
        <a href="/<?=$backlink?>/" class="btn btn-success" role="button">вернуться назад</a>
    </div>
<?} else { ?>

    <script type="text/javascript">
        var bbcrow = '';
    	var bbclink = '';
        function updateBbcRow() {
            $('table.regtable tr#bla-bla-car-tr-basic').remove();
            if (bbcrow) {
                if ($('table.regtable tbody tr:visible').length > 2) {
                    $('table.regtable tbody tr:visible').eq(1).after(bbcrow);
                } else {
                    $('table.regtable tbody').append(bbcrow);
                }
                $('table.regtable tr#bla-bla-car-tr-basic').click(function() {
                    var href = $(this).find("a").attr("href");
                    if (href) {
                    	yaCounter26673519.reachGoal('BBC_BLOCK_CLICK');
                        window.location = href;
                    }
                });
            }
        }
        function setBbcLink(href) {
        	var tree = $('<tbody>' + bbcrow + '</tbody>');
        	$(tree).find("a").attr("href", href);
        	bbcrow = $(tree).html();
        }
        document.addEventListener('DOMContentLoaded', function() {
            jQuery(function ($) {
                var posting = $.post('/bbc.php', {
                    from: '<?=$info['from_town']?>',
                    to: '<?=$info['to_town']?>',
                    alias: '<?=$info['thread_alias']?>',
                    from_alias: '<?=$info['from_town_alias']?>'
                });
                posting.done(function(data) {
                    bbcrow = data;
                    bbclink = $(data).find("a").attr("href");
                    updateBbcRow();
                });
                $('#date_input').change(function(){
                    if ($(this).val() == '') {
                    	$('table.regtable tr').show();
                    	setBbcLink(bbclink);
                    } else {
                    	var db = $(this).val().replace(/\./g, '/');
                   		setBbcLink(bbclink + '&db=' + db);
                    }
                    updateBbcRow();
                });
            });
        });
    </script>

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
