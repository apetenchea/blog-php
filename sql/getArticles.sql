drop procedure if exists getArticles;
delimiter ;;

/*
 * Get all articles which belong to all the provided categories.
 * The list of categories comes in the form of a string, categories separated
 * by a comma, like this 'secrity,math,algorithms'.
 * If the argument is null, all articles are returned.
 * The interval_start and interval_size parameters are used for pagination.
 */
create procedure getArticles(in category_list varchar(256), in interval_start int, in interval_size int)
begin
	/* Keeps the state of the loop. */
	declare done int default 0;

	/* 1 if no category what yet added to the query. */ 
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
			/* Trick to get an empty set. */
			select null limit 0;
		else
			set @query := concat(@query,') order by id desc limit ', interval_start, ',', interval_size, ';');
			prepare stmt from @query;
			execute stmt;
			deallocate prepare stmt;
		end if;
	end if;
end;;

delimiter ;
