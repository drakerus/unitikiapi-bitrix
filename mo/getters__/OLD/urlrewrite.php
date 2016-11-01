<?
$arUrlRewrite = array(
	array(
    "CONDITION" => "#^/moscow/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=moscow&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/moscow/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=moscow&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/moscow/#",
	"RULE" => "alias=moscow&page=index",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/istra/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=istra&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/istra/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=istra&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/istra/#",
	"RULE" => "alias=istra&page=index",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/luhovici/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=luhovici&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/luhovici/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=luhovici&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/luhovici/#",
	"RULE" => "alias=luhovici&page=index",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/liktarino/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=liktarino&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/liktarino/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=liktarino&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/liktarino/#",
	"RULE" => "alias=liktarino&page=index",
    "PATH" => "/town/index.php",
	),
	/*KLIN*/
		array(
    "CONDITION" => "#^/klin/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=klin&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/klin/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=klin&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/klin/#",
	"RULE" => "alias=klin&page=index",
    "PATH" => "/town/index.php",
	),
	
	/*shahovskaja*/
		array(
    "CONDITION" => "#^/shahovskaja/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=shahovskaja&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/shahovskaja/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=shahovskaja&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/shahovskaja/#",
	"RULE" => "alias=shahovskaja&page=index",
    "PATH" => "/town/index.php",
	),
	
	/*schelkovo*/
		array(
    "CONDITION" => "#^/schelkovo/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=schelkovo&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/schelkovo/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=schelkovo&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/schelkovo/#",
	"RULE" => "alias=schelkovo&page=index",
    "PATH" => "/town/index.php",
	),	
		
	/*shatura*/
		array(
    "CONDITION" => "#^/shatura/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=shatura&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/shatura/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=shatura&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/shatura/#",
	"RULE" => "alias=shatura&page=index",
    "PATH" => "/town/index.php",
	),
	
	/*kashira*/
		array(
    "CONDITION" => "#^/kashira/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=kashira&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/kashira/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=kashira&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/kashira/#",
	"RULE" => "alias=kashira&page=index",
    "PATH" => "/town/index.php",
	),
			
	/*korolev*/
		array(
    "CONDITION" => "#^/korolev/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=korolev&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/korolev/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=korolev&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/korolev/#",
	"RULE" => "alias=korolev&page=index",
    "PATH" => "/town/index.php",
	),
	
	/*ruza*/
	array(
    "CONDITION" => "#^/ruza/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ruza&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ruza/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ruza&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ruza/#",
	"RULE" => "alias=ruza&page=index",
    "PATH" => "/town/index.php",
	),
	
	/*ozeri*/
	array(
    "CONDITION" => "#^/ozeri/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ozeri&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ozeri/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ozeri&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ozeri/#",
	"RULE" => "alias=ozeri&page=index",
    "PATH" => "/town/index.php",
	),
	
	/*ramenskoe*/
	array(
    "CONDITION" => "#^/ramenskoe/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ramenskoe&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ramenskoe/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ramenskoe&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ramenskoe/#",
	"RULE" => "alias=ramenskoe&page=index",
    "PATH" => "/town/index.php",
	),
	
		/*chehov*/
	array(
    "CONDITION" => "#^/chehov/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=chehov&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/chehov/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=chehov&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/chehov/#",
	"RULE" => "alias=chehov&page=index",
    "PATH" => "/town/index.php",
	),
	
			/*taldom*/
	array(
    "CONDITION" => "#^/taldom/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=taldom&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/taldom/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=taldom&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/taldom/#",
	"RULE" => "alias=taldom&page=index",
    "PATH" => "/town/index.php",
	),
	
				/*stupino*/
	array(
    "CONDITION" => "#^/stupino/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=stupino&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/stupino/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=stupino&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/stupino/#",
	"RULE" => "alias=stupino&page=index",
    "PATH" => "/town/index.php",
	),
	
				/*serpuhov*/
	array(
    "CONDITION" => "#^/serpuhov/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=serpuhov&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/serpuhov/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=serpuhov&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/serpuhov/#",
	"RULE" => "alias=serpuhov&page=index",
    "PATH" => "/town/index.php",
	),
	
					/*noginsk*/
	array(
    "CONDITION" => "#^/noginsk/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=noginsk&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/noginsk/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=noginsk&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/noginsk/#",
	"RULE" => "alias=noginsk&page=index",
    "PATH" => "/town/index.php",
	),
	
					/*mitischi*/
	array(
    "CONDITION" => "#^/mitischi/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=mitischi&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/mitischi/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=mitischi&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/mitischi/#",
	"RULE" => "alias=mitischi&page=index",
    "PATH" => "/town/index.php",
	),
	
						/*luberci*/
	array(
    "CONDITION" => "#^/luberci/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=luberci&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/luberci/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=luberci&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/luberci/#",
	"RULE" => "alias=luberci&page=index",
    "PATH" => "/town/index.php",
	),
		
							/*ivanteevka*/
	array(
    "CONDITION" => "#^/ivanteevka/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ivanteevka&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ivanteevka/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=ivanteevka&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/ivanteevka/#",
	"RULE" => "alias=ivanteevka&page=index",
    "PATH" => "/town/index.php",
	),
	
								/*jukovskij*/
	array(
    "CONDITION" => "#^/jukovskij/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=jukovskij&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/jukovskij/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=jukovskij&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/jukovskij/#",
	"RULE" => "alias=jukovskij&page=index",
    "PATH" => "/town/index.php",
	),
		
									/*domodedovo*/
	array(
    "CONDITION" => "#^/domodedovo/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=domodedovo&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/domodedovo/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=domodedovo&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/domodedovo/#",
	"RULE" => "alias=domodedovo&page=index",
    "PATH" => "/town/index.php",
	),
	
			
									/*dolgoprudnij*/
	array(
    "CONDITION" => "#^/dolgoprudnij/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=dolgoprudnij&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/dolgoprudnij/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=dolgoprudnij&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/dolgoprudnij/#",
	"RULE" => "alias=dolgoprudnij&page=index",
    "PATH" => "/town/index.php",
	),
	
										/*dmitrov*/
	array(
    "CONDITION" => "#^/dmitrov/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=dmitrov&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/dmitrov/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=dmitrov&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/dmitrov/#",
	"RULE" => "alias=dmitrov&page=index",
    "PATH" => "/town/index.php",
	),
	
											/*volokolamsk*/
	array(
    "CONDITION" => "#^/volokolamsk/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=volokolamsk&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/volokolamsk/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=volokolamsk&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/volokolamsk/#",
	"RULE" => "alias=volokolamsk&page=index",
    "PATH" => "/town/index.php",
	),
	
		
											/*pavlovskij-posad*/
	array(
    "CONDITION" => "#^/pavlovskij-posad/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=pavlovskij-posad&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/pavlovskij-posad/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=pavlovskij-posad&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/pavlovskij-posad/#",
	"RULE" => "alias=pavlovskij-posad&page=index",
    "PATH" => "/town/index.php",
	),
	
												/*elektrostal*/
	array(
    "CONDITION" => "#^/elektrostal/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=elektrostal&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/elektrostal/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=elektrostal&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/elektrostal/#",
	"RULE" => "alias=elektrostal&page=index",
    "PATH" => "/town/index.php",
	),
	
													/*orehovo-zuevo*/
	array(
    "CONDITION" => "#^/orehovo-zuevo/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=orehovo-zuevo&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/orehovo-zuevo/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=orehovo-zuevo&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/orehovo-zuevo/#",
	"RULE" => "alias=orehovo-zuevo&page=index",
    "PATH" => "/town/index.php",
	),
	
														/*podolsk*/
	array(
    "CONDITION" => "#^/podolsk/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=podolsk&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/podolsk/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=podolsk&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/podolsk/#",
	"RULE" => "alias=podolsk&page=index",
    "PATH" => "/town/index.php",
	),
	
	
															/*egorevsk*/
	array(
    "CONDITION" => "#^/egorevsk/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=egorevsk&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/egorevsk/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=egorevsk&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/egorevsk/#",
	"RULE" => "alias=egorevsk&page=index",
    "PATH" => "/town/index.php",
	),
	
		
															/*bronnici*/
	array(
    "CONDITION" => "#^/bronnici/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=bronnici&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/bronnici/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=bronnici&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/bronnici/#",
	"RULE" => "alias=bronnici&page=index",
    "PATH" => "/town/index.php",
	),
	
																/*voskresensk*/
	array(
    "CONDITION" => "#^/voskresensk/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=voskresensk&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/voskresensk/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=voskresensk&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/voskresensk/#",
	"RULE" => "alias=voskresensk&page=index",
    "PATH" => "/town/index.php",
	),
	
																	/*kolomna*/
	array(
    "CONDITION" => "#^/kolomna/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=kolomna&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/kolomna/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=kolomna&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/kolomna/#",
	"RULE" => "alias=kolomna&page=index",
    "PATH" => "/town/index.php",
	),
	
		
																	/*sergiev-posad*/
	array(
    "CONDITION" => "#^/sergiev-posad/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=sergiev-posad&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/sergiev-posad/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=sergiev-posad&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/sergiev-posad/#",
	"RULE" => "alias=sergiev-posad&page=index",
    "PATH" => "/town/index.php",
	),
	
																		/*balashiha*/
	array(
    "CONDITION" => "#^/balashiha/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=balashiha&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/balashiha/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=balashiha&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/balashiha/#",
	"RULE" => "alias=balashiha&page=index",
    "PATH" => "/town/index.php",
	),
	
	
																			/*solnechnogorsk*/
	array(
    "CONDITION" => "#^/balashiha/station/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=balashiha&page=station&station=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/balashiha/bus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=balashiha&page=bus&bus=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/balashiha/#",
	"RULE" => "alias=balashiha&page=index",
    "PATH" => "/town/index.php",
	),	
	
);
?>