<script type="text/javascript">
function go_to_town(){
var town_array = new Array();
town_array["Москва"]='moscow';
town_array["Щелково"]='schelkovo';
town_array["Шаховская"]='shahovskaja';
town_array["Шатура"]='shatura';
town_array["Чехов"]='chehov';
town_array["Химки"]='himki';
town_array["Талдом"]='taldom';
town_array["Ступино"]='stupino';
town_array["Солнечногорск"]='solnechnogorsk';
town_array["Серпухов"]='serpuhov';
town_array["Руза"]='ruza';
town_array["Раменское"]='ramenskoe';
town_array["Озёры"]='ozeri';
town_array["Ногинск"]='noginsk';
town_array["Мытищи"]='mitischi';
town_array["Можайск"]='mojaisk';
town_array["Люберцы"]='luberci';
town_array["Лыткарино"]='liktarino';
town_array["Луховицы"]='luhovici';
town_array["Клин"]='klin';
town_array["Кашира"]='kashira';
town_array["Королёв"]='korolev';
town_array["Истра"]='istra';
town_array["Ивантеевка"]='ivanteevka';
town_array["Жуковский"]='jukovskij';
town_array["Егорьевск"]='egorevsk';
town_array["Домодедово"]='domodedovo';
town_array["Долгопрудный"]='dolgoprudnij';
town_array["Дмитров"]='dmitrov';
town_array["Волоколамск"]='volokolamsk';
town_array["Видное"]='vidnoe';
town_array["Бронницы"]='bronnici';
town_array["Балашиха"]='balashiha';
town_array["Павловский Посад"]='pavlovskij-posad';
town_array["Электросталь"]='elektrostal';
town_array["Орехово-Зуево"]='orehovo-zuevo';
town_array["Сергиев Посад"]='sergiev-posad';
town_array["Коломна"]='kolomna';
town_array["Воскресенск"]='voskresensk';
town_array["Подольск"]='podolsk';
var town_alias = town_array[$('#town').val()];
var prefix='http://mybuses.ru/';
var postfixxx='/';
var src=prefix+town_alias+postfixxx;
window.location.href = src;
}
</script>
