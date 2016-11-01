<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingOne">
			Легенда:  
			<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne"><span class="glyphicon glyphicon-chevron-up"></span>свернуть</a>		
		</div>
		<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
			<div class="panel-body">
			<?php foreach ($station_race_types as $id => $race_type) { ?>
				<span class="label label-sh sh<?=$id?>"><?=$race_type?></span>
			<?php } ?>
			</div>
		</div>		
	</div>
</div>