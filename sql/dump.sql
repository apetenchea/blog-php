-- MySQL dump 10.13  Distrib 5.5.58, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: cdroot
-- ------------------------------------------------------
-- Server version	5.5.58-0+deb8u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `cdroot`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `cdroot` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `cdroot`;

--
-- Table structure for table `ArticleCategory`
--

DROP TABLE IF EXISTS `ArticleCategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ArticleCategory` (
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  KEY `article_id` (`article_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `ArticleCategory_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `Articles` (`id`),
  CONSTRAINT `ArticleCategory_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ArticleCategory`
--

LOCK TABLES `ArticleCategory` WRITE;
/*!40000 ALTER TABLE `ArticleCategory` DISABLE KEYS */;
INSERT INTO `ArticleCategory` VALUES (5,7),(6,8),(7,8),(8,1),(8,7);
/*!40000 ALTER TABLE `ArticleCategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Articles`
--

DROP TABLE IF EXISTS `Articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Articles`
--

LOCK TABLES `Articles` WRITE;
/*!40000 ALTER TABLE `Articles` DISABLE KEYS */;
INSERT INTO `Articles` VALUES (5,'pe-format'),(6,'tail-end-recursion'),(7,'the-basics-of-llvm-passes'),(8,'hidden-imported-procedures');
/*!40000 ALTER TABLE `Articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Categories`
--

DROP TABLE IF EXISTS `Categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Categories`
--

LOCK TABLES `Categories` WRITE;
/*!40000 ALTER TABLE `Categories` DISABLE KEYS */;
INSERT INTO `Categories` VALUES (1,'security'),(2,'algorithms'),(3,'mathematics'),(4,'software'),(5,'networking'),(6,'psychology'),(7,'windows'),(8,'programming');
/*!40000 ALTER TABLE `Categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `CategoriesView`
--

DROP TABLE IF EXISTS `CategoriesView`;
/*!50001 DROP VIEW IF EXISTS `CategoriesView`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `CategoriesView` (
  `id` tinyint NOT NULL,
  `name` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'cdroot'
--
/*!50003 DROP PROCEDURE IF EXISTS `getArticles` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`cdroot`@`localhost` PROCEDURE `getArticles`(in category_list varchar(256), in interval_start int, in interval_size int)
begin
	
	declare done int default 0;

	 
	declare first int default 1;

	declare category_name varchar(32) default '';
	declare category_id int default 0;
	declare categories_cursor cursor for select id, name from Categories;
	declare continue handler for not found set done = 1;

	if category_list is null then
		select name from Articles order by id desc limit interval_start, interval_size;
	else
		set @query :=
			'select name from Articles where id in (select article_id from ArticleCategory';

		open categories_cursor;
		get_categories: loop
			fetch categories_cursor into category_id, category_name;
			if done = 1 then
				leave get_categories;
			end if;
			if locate(category_name, category_list) != 0 then
				if first = 1 then
					set first = 0;
					set @query = concat(@query, ' where article_id');
				else
					set @query = concat(@query, ' and article_id');
				end if;
				set @query = concat(
					@query,
					' in (select article_id from ArticleCategory where category_id = ',
					quote(category_id),
					')');
			end if;
		end loop get_categories;
		close categories_cursor;

		if first = 1 then
			
			select null limit 0;
		else
			set @query := concat(@query,') order by id desc limit ', interval_start, ',', interval_size, ';');
			prepare stmt from @query;
			execute stmt;
			deallocate prepare stmt;
		end if;
	end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `getCategories` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`cdroot`@`localhost` PROCEDURE `getCategories`(in article_name varchar(128))
begin
	if (article_name is null) then
		select name from CategoriesView;
	else
		select name from CategoriesView as cv
		inner join ArticleCategory as ac on cv.id = ac.category_id
			and ac.article_id in (select id from Articles where name = article_name);
	end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Current Database: `cdroot`
--

USE `cdroot`;

--
-- Final view structure for view `CategoriesView`
--

/*!50001 DROP TABLE IF EXISTS `CategoriesView`*/;
/*!50001 DROP VIEW IF EXISTS `CategoriesView`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`cdroot`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `CategoriesView` AS select `category`.`id` AS `id`,`category`.`name` AS `name` from (`Categories` `category` join `ArticleCategory` `ac` on((`category`.`id` = `ac`.`category_id`))) group by `category`.`name` order by count(`ac`.`category_id`) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-11 16:35:58
