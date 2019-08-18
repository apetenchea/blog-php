drop procedure if exists getCategories;
delimiter ;;

/*
 * Get the categories of an article, ordered by the number of articles belonging to each one of them.
 * If article_name is null, all categories are returned.
 */
create procedure getCategories(in article_name varchar(128))
begin
	if (article_name is null) then
		select name from CategoriesView;
	else
		select name from CategoriesView as cv
		inner join ArticleCategory as ac on cv.id = ac.category_id
			and ac.article_id in (select id from Articles where name = article_name);
	end if;
end;;

delimiter ;
