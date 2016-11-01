<?php defined( 'ROOT_PATH' ) or die();?>
<div class="col-md-12">
    <h3>Междугородние автостанции <?=$town['town_name'];?></h3>
    <h4>Поиск по автобусам</h4>
    <div class="alert alert-bus alert-block alert-danger"></div>
    <div class="input-group">
        <span class="input-group-btn"><button class="btn btn-info disabled" type="button">автостанция <?=$terminus['station_name'];?> <span class="glyphicon glyphicon-arrow-right"></span></button></span>
        <input id="reg_bus_typeahead" type="text" class="form-control" placeholder="место назначения" autocomplete="off" />
    </div>

    <div class="search_description">
        <span class="glyphicon glyphicon-info-sign"></span>&nbsp;Воспользуйтесь быстрым поиском по автобусам: начните вводить интересующий маршрут и просто выберите его из списка
    </div>
</div>
<div class="col-md-12">
    <div class="panel panel-info">
        <div class="panel-heading"><h3><a href="/<?=$town['town_alias'];?>/terminus/<?=$terminus['alias']?>/" title="Расписание автобусов <?=htmlspecialchars($town['town_name']);?> автостанции <?=htmlspecialchars($terminus['station_name']);?> <?=htmlspecialchars($town['town_name']);?>"><?=$town['town_name'];?> автостанция <?=$terminus['station_name'];?></a></h3></div>
        <div class="panel-body">
            <div class="list-group">
                <? foreach( $terminus['ways'] as $way ){ ?>
                    <a href="/<?=$town['town_alias'];?>/way/<?=$way['thread_alias'];?>/"  class='list-group-item'  title="автобус <?=htmlspecialchars($way['seo_thread_name']);?> расписание" ><?=$way['seo_thread_name'];?></a>
                <?}?>
            </div>
        </div>
    </div>

</div>