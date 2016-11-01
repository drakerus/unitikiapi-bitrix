<div class="row">
	<div class="col-md-12">
		<h4>Поиск по остановкам</h4>
		<div class="alert alert-station alert-block alert-danger"></div>
		<div class="input-group">
			<span class="input-group-btn"><button class="btn btn-info" type="button"><span class="glyphicon glyphicon-arrow-right"></span></button></span>
			<input id="station_typehead" type="text" class="col-md-12 form-control" placeholder="Остановка..." autocomplete="off" />
		</div>
	</div>
	<div class="col-md-12">
		<div class="search_description">
			<span class="glyphicon glyphicon-info-sign"></span>
			Воспользуйтесь быстрым поиском по остановкам: начните вводить название интересующей остановки и просто выберите её из списка
		</div>
	</div>
</div>
<div class="row visible-lg-block">
	<div class="col-md-12">
		<div class="panel panel-info">
			<div class="panel-heading">
				<h3>Все остановки в г.<?=$town->town_name?> | <small><a href="#bs" title="Расписание автобусов г.<?=$town->town_name_escaped?>" ><span class="glyphicon glyphicon-arrow-right"></span> Все автобусы</a></small></h3>
			</div>
			<div class="panel-body">
				<div class="row">
				<?php foreach ($stations_by_col as $stations_part) { ?>
					<div class="col-md-3">
						<div class="list-group">
						<?php foreach ($stations_part as $station) { ?>
							<a href="/<?=$town->town_alias?>/station/<?=$station->alias?>/" class="list-group-item" title="Расписание автобусов на остановке <?=$station->name_escaped?>"><?=$station->name?></a>
						<?php } ?>
						</div>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>