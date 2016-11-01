<?php defined( 'ROOT_PATH' ) or die();?>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
   <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingregions">
      <h4>
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#regions" aria-expanded="false" aria-controls="regions"><span class="glyphicon glyphicon-arrow-down"></span> Расписание междугородних рейсов, города отправления: <span class="glyphicon glyphicon-arrow-down"></span></a>
      </h4>
    </div>
    <div id="regions" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingregions">
      <div class="panel-body">
		  <div class="row">
			<div class="col-md-3">
			<? foreach($towns as $idx => $town){ ?>
					<h5><span class="glyphicon glyphicon-map-marker"></span> <a href="/<?=$town['town_alias'];?>/"  title="<?=htmlspecialchars($town['town_name']);?> расписание автобусов"><?=$town['town_name'];?></a></h5>
			<? if( ($idx+1)%$per_col == 0 ){?>
			</div>
			<div class="col-md-3">
				<?}
			}?>
			</div>
		</div>
	 </div>
    </div>
  </div>
</div>