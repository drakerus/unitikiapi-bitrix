<a name="bs"></a>
<div class="row">
	<div class="col-md-12">
		<h4>Поиск по автобусам</h4>
		<div class="alert alert-bus alert-block alert-danger"></div>
		<div class="input-group">
			<span class="input-group-btn"><button class="btn btn-info" type="button"><span class="glyphicon glyphicon-arrow-right"></span></button></span>
			<input id="bus_typeahead" type="text" class="col-md-12 form-control" placeholder="Автобус.." autocomplete="off"/>
		</div>
	</div>
	<div class="col-md-12">
		<div class="search_description">
			<span class="glyphicon glyphicon-info-sign"></span>
			Воспользуйтесь быстрым поиском по автобусам: начните вводить интересующий маршрут и просто выберите его из списка
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-info">
			<div class="panel-heading">
				<h3>Все автобусы г.<?=$town->town_name?> | 
					<?php if ($town->is_reg_town) { ?>
						<small><a href="#rbs" title="Расписание междугородних автобусов из г.<?=$town->town_name_escaped?>"><span class="glyphicon glyphicon-arrow-right"></span> Междугородние автобусы</a></small>
					<?php } else { ?>
						<small><a href="#st" title="Расписание автобусов по всем остановкам г.<?=$town->town_name_escaped?>"><span class="glyphicon glyphicon-arrow-right"></span> Все остановки</a></small>
					<?php } ?>
				</h3>
			</div>
		</div>
	</div>
</div>
<div class="row">
<?php foreach($buses_by_type as $bus_type => &$buses_part) { ?>
	<div class="col-md-<?=$n?>">
		<div class="panel panel-info">
			<div class="panel-heading capitalize">
				<h2><?=$bus_type?></h2>
			</div>
			<div class="panel-body">
			<?php if (count($buses_by_type) === 1 && $bus_types[1] === $bus_type) { ?>
				<?php foreach (array_chunk($buses_part, ceil(count($buses_part)/4)) as $chunk) { ?>
					<div class="col-md-3">
						<div class="list-group">
						<?php foreach($chunk as $bus) { ?>
							<a  href="/<?=$town->town_alias?>/bus/<?=$bus->alias?>/" class="list-group-item" title="Расписание автобуса №<?=$bus->name_escaped?>"><?=$bus->name?></a>
						<?php } ?>
						</div>
					</div>
				<?php } ?>
			<?php } else { ?>
				<div class="list-group">
				<?php foreach($buses_part as $bus) { ?>
					<a  href="/<?=$town->town_alias?>/bus/<?=$bus->alias?>/" class="list-group-item" title="Расписание автобуса №<?=$bus->name_escaped?>"><?=$bus->name?></a>
				<?php } ?>
				</div>
			<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>
</div>
