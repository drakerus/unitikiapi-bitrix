<div class="row"><div class="col-md-6">
        <h4>Вокзалы отправления:</h4>
        <? foreach ($terminus['from'] as $thread) { ?>
        <a class="btn btn-default" href="/<?=$thread['from_town_alias'];?>/way/<?=$thread_alias;?>/#terminus_<?=$thread['from_station_alias'];?>" role="button">
            <?=mb_substr( ( (stristr($thread['from_station'], $thread['from_town'])) ? $thread['from_station'] : $thread['from_town'].' '.$thread['from_station'] ), 0, 50);?>
        </a>
        <?}?>
    </div>
    <div class="col-md-6">
        <h4>Вокзалы прибытия:</h4>
        <? foreach ($terminus['to']  as $thread) { ?>
            <a class="btn btn-default" href="#terminus_<?=$thread['to_station_alias'];?>" role="button">
                <?=mb_substr(  ( (stristr($thread['to_station'], $thread['to_town'])) ? $thread['to_station'] : $thread['to_town'].' '.$thread['to_station'] ), 0, 50);?>
            </a>
        <?}?>
    </div>
</div>
