<div class="row">
	<div class="col-md-12">
		<h2>
			Расписание автобусов на остановке <?=$station->name?>
			<?php if ($race_direction == 0) { ?>
				<small>
					(от вокзала)
					<a href="#br" title="<?=$station->name_escaped?> - к вокзалу"><span class="glyphicon glyphicon-arrow-right"></span> (к вокзалу)</a>
				</small>
			<?php } else { ?>
				<small>
					(к вокзалу)
					<a href="#fr" title="<?=$station->name_escaped?> - от вокзала"><span class="glyphicon glyphicon-arrow-right"></span> (от вокзала)</a>
				</small>
			<?php } ?>
		</h2>
		
		<?=$race_direction == 0 ? '<a name="fr"></a>' : '<a name="br"></a>'?>
		
		<table  class="table table-bordered" id="table<?=$race_direction == 0 ? 1 : 2 ?>">
		<thead>
			<tr>
				<?php foreach ($buses as $bus) { ?>
					<th class="sh_th_<?=$bus->alias?>">
						<a href="/<?=$town->town_alias?>/bus/<?=$bus->alias?>/" title="Расписание автобуса <?=$bus->name_escaped?>"><?=$bus->bus_name?></a>
					</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<tr>
			<?php foreach ($times as $rows) { ?>
				<td>
					<ul class="list-unstyled sh_ul">
					<?php foreach ($rows as $row) { ?>
						<li class="sh<?=$row->id_race_type?>"><?=$row->time?></li>
					<?php } ?>
					</ul>
				</td>
			<?php } ?>
			</tr>
		</tbody>
		</table>
	</div>
</div>