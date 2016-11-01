<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingOne">    
			Выбор остановок  
			<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$race_direction == 0 ? 'f' : 'b'?>" aria-expanded="true" aria-controls="collapse<?=$race_direction == 0 ? 'f' : 'b'?>"><i class="indicator glyphicon glyphicon-chevron-down"></i>развернуть</a>
		</div>
		<div id="collapse<?=$race_direction == 0 ? 'f' : 'b'?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
			<div class="panel-body">
				<span class="glyphicon glyphicon-arrow-right"></span> 
				Остановки по которым следует маршрут №<?=$bus->name?> 
				<small>(от вокзала)</small> 
				<span class="glyphicon glyphicon-arrow-right"></span>
				<br/>
				<button type="button" class="btn btn-success show-all-columns" data-targettable="table<?=$race_direction == 0 ? 1 : 2 ?>">Показать все</button>
				<?php foreach ($stations as $station) { ?>
					<button type="button" class="btn btn-default show-hide-column" data-toggle="button" data-targettable="table<?=$race_direction == 0 ? 1 : 2 ?>" data-busnumber="<?=$station->alias?>"><?=$station->name?></button>
				<?php } ?>
			</div>		
		</div>
	</div>	
</div>