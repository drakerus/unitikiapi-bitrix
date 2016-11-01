<div class="row">
	<div class="col-md-12">
		<h2>
			Расписание автобуса №<?=$bus->bus_name?>: 
			<?php if ($race_direction == 0) { ?>
				<?=$bus->f_way?> 
				<small>
					(от вокзала)
					<a href="#br" title="<?=$bus->b_way?>"><span class="glyphicon glyphicon-arrow-right"></span> (к вокзалу)</a>
				</small>
			<?php } else { ?>
				<?=$bus->b_way?> 
				<small>
					(к вокзалу)
					<a href="#fr" title="<?=$bus->f_way?>"><span class="glyphicon glyphicon-arrow-right"></span> (от вокзала)</a>
				</small>
			<?php } ?>
		</h2>

		<?=$race_direction == 0 ? '<a name="fr"></a>' : '<a name="br"></a>'?>

		<table  class="table table-bordered" id="table<?=$race_direction == 0 ? 1 : 2 ?>">
		<thead>
			<tr>
				<th class="sh_th_"><noindex>График / Остановка</noindex></th>
				<?php foreach ($stations as $station) { ?>
					<th class="sh_th_<?=$station->alias?>">
						<a href="/<?=$town->town_alias?>/station/<?=$station->alias?>/" title="Расписание автобусов на остановке <?=$station->name_escaped?>"><?=$station->name?></a>
					</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>

			<?php foreach ($rows as $k2 => $row) { ?>
			<tr class="sh<?=$row->id_race_type?> <?=$row->payment_type == 1 ? 'pay' : ''?> columnIsVisible <?=$k2?>">

				<td><noindex><?=$row->race_human_value?></noindex></td>
				<?php foreach ($stations_ids as $id) { ?>
					<td><?=isset($times[$k2], $times[$k2][$id]) ? $times[$k2][$id] : '&nbsp;'?></td>
				<?php } ?>

			</tr>
			<?php } ?>
		
		</tbody>
		</table>	
	</div>
</div>