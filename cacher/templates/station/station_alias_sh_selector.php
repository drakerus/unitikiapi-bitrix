<div class="panel panel-default">
	<div class="panel-heading">Выберите интересующие вас автобусы</div>
	<div class="panel-body">
		Все автобусы следующие через остановку <?=$station->name?>  <small>(<?=$race_direction == 0 ? 'от вокзала' : 'к вокзалу'?>)</small><br/>
		<button type="button" class="btn btn-success show-all-columns" data-targettable="table<?=$race_direction == 0 ? 1 : 2 ?>">Показать все</button>
		<?php foreach ($buses as $bus) { ?>
			<button type="button" class="btn btn-default show-hide-column" data-toggle="button" data-targettable="table<?=$race_direction == 0 ? 1 : 2 ?>" data-busnumber="<?=$bus->alias?>"><?=$bus->bus_name?></button>
		<?php } ?>
	</div>
</div>