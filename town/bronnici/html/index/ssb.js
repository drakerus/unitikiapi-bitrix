<script type="text/javascript">
$(function(){
	function displayResultBus(item){
		if(item.text!=='Result not Found'){
			var prefix='http://mybuses.ru/';
			var postfix='/bus/';
			var postfixxx='/';
			var town='bronnici';
			var src=prefix+town+postfix+item.value+postfixxx;window.open(src, "_self");
		}else{
			$('.alert-bus').show().html('Увы ничего не найдено, попытайтесь снова');
		}
	}
	$('#bus_typeahead').typeahead({
		source:[{"id":"1","name":"1 - м\/р Москворечье – с\/х Бронницкий"},{"id":"2","name":"2 - м\/р Москворечье – Поликлиника"},{"id":"4","name":"4 - Автостанция – м-н Карусель – Автостанция"},{"id":"5","name":"5 - ул. Москворецкая – Марьинка – ул. Москворецкая"},{"id":"24","name":"24 - Бронницы – Фоминское"},{"id":"30","name":"30 - Бронницы –  Нащекино"},{"id":"35","name":"35 - Бронницы – Натальино"},{"id":"56","name":"56 - Бронницы – м\/р Горка"},{"id":"57","name":"57 - Бронницы – Коломна (а\/в Коломна)"},{"id":"59","name":"59 - Бронницы – Бельково"},{"id":"324","name":"324 - Бронницы – м. Котельники"},{"id":"416","name":"416 - Константиново – м. Котельники"},{"id":"978","name":"978 - Беспятово – м. Котельники"}],
		onSelect: displayResultBus
	});
});
</script>