<div class="btn-group" data-toggle="buttons">  
  <label class="btn btn-primary" OnClick='sh_select("noday")'><input type="radio" name="options" >Любой день</label>
  <label class="btn btn-primary" OnClick='sh_select("mo")'><input type="radio" name="options" >Пн</label>
  <label class="btn btn-primary" OnClick='sh_select("tu")'><input type="radio" name="options" >Вт</label>
  <label class="btn btn-primary" OnClick='sh_select("we")'><input type="radio" name="options" >Ср</label>
  <label class="btn btn-primary" OnClick='sh_select("th")'><input type="radio" name="options" >Чт</label>
  <label class="btn btn-primary" OnClick='sh_select("fr")'><input type="radio" name="options" >Пт</label>
  <label class="btn btn-primary" OnClick='sh_select("sa")'><input type="radio" name="options" >Сб</label>
  <label class="btn btn-primary" OnClick='sh_select("su")'><input type="radio" name="options" >Вс</label>
  <!--
  <label class="btn btn-primary" OnClick='sh_select("weekday")' ><input type="radio" name="options" >Будни</label>
  <label class="btn btn-primary" OnClick='sh_select("weekend")'><input type="radio" name="options">Выходные</label>
  <label class="btn btn-primary" OnClick='sh_select("everyday")'><input type="radio" name="options" checked>Ежедневно</label> -->
</div>
<script type="text/javascript">
<?include($_SERVER['DOCUMENT_ROOT'].'/mods/bus/include/selector_legend.js');?>
</script>