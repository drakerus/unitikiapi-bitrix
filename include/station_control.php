<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
	<div class="panel panel-default">
		<div class="panel-heading" role="tab" id="headingOne">    
		Выбор остановок  
		<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?=$dn;?>" aria-expanded="true" aria-controls="collapse<?=$dn;?>">
		<!--<span class="glyphicon glyphicon-chevron-down"></span>--><i class="indicator glyphicon glyphicon-chevron-down"></i>развернуть
		</a>
		</div>
		<div id="collapse<?=$dn;?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
			<div class="panel-body">
			<? include($dr.'/include/transcription_bus_'.$dn.'.php');?><br/>
			<? include($bus_control); ?>
			</div>
		</div>		
	</div>
</div>