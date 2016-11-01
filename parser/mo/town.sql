/*
SQLyog Community v12.12 (64 bit)
MySQL - 5.6.17 : Database - ph382841_mybuses
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `town` */

DROP TABLE IF EXISTS `town`;

CREATE TABLE `town` (
  `id_town` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `town_name` varchar(100) NOT NULL COMMENT 'имя города',
  `town_alias` varchar(100) NOT NULL COMMENT 'алиас',
  `mta_href` varchar(300) NOT NULL COMMENT 'ссылка на источник для мострансавто',
  `active` int(2) NOT NULL COMMENT 'активность',
  PRIMARY KEY (`id_town`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

/*Data for the table `town` */

LOCK TABLES `town` WRITE;

insert  into `town`(`id_town`,`town_name`,`town_alias`,`mta_href`,`active`) values (1,'Москва','moscow','',0),(42,'Щелково','schelkovo','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=40',1),(43,'Шаховская','shahovskaja','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=43',1),(44,'Шатура','shatura','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=39',1),(45,'Чехов','chehov','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=38',1),(46,'Химки','himki','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=37',1),(47,'Талдом','taldom','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=36',1),(48,'Ступино','stupino','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=35',1),(49,'Солнечногорск','solnechnogorsk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=34',1),(50,'Серпухов','serpuhov','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=33',1),(51,'Руза','ruza','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=31',1),(52,'Раменское','ramenskoe','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=30',1),(53,'Озёры','ozeri','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=26',1),(54,'Ногинск','noginsk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=24',1),(55,'Мытищи','mitischi','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=22',1),(56,'Можайск','mojaisk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=21',1),(57,'Люберцы','luberci','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=20',1),(58,'Лыткарино','liktarino','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=19',1),(59,'Луховицы','luhovici','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=18',1),(60,'Клин','klin','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=16',1),(61,'Кашира','kashira','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=15',1),(62,'Королёв','korolev','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=14',1),(63,'Истра','istra','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=13',1),(64,'Ивантеевка','ivanteevka','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=12',1),(65,'Жуковский','jukovskij','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=10',1),(66,'Егорьевск','egorevsk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=9',1),(67,'Домодедово','domodedovo','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=8',1),(68,'Долгопрудный','dolgoprudnij','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=7',1),(69,'Дмитров','dmitrov','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=6',1),(70,'Волоколамск','volokolamsk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=4',1),(71,'Видное','vidnoe','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=3',1),(72,'Бронницы','bronnici','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=2',1),(73,'Балашиха','balashiha','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=1',1),(74,'Павловский Посад','pavlovskij-posad','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=28',1),(75,'Электросталь','elektrostal','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=41',1),(76,'Орехово-Зуево','orehovo-zuevo','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=27',1),(77,'Сергиев Посад','sergiev-posad','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=32',1),(78,'Коломна','kolomna','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=17',1),(79,'Воскресенск','voskresensk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=5',1),(80,'Подольск','podolsk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=29',1),(81,'Пермь','perm','',1),(82,'Наро-Фоминск','naro-fominsk','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=23',1),(83,'Одинцово','odincovo','http://www.mostransavto.ru/passengers/routes/raspisaniya/?page=patp&ak=25',1);

UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
