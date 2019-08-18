drop view if exists CategoriesView;

/*
 * Get all categories, ordered by the number of articles belonging to them.
 */
create view CategoriesView as
	select category.id, category.name from Categories as category
		inner join ArticleCategory as ac on category.id = ac.category_id
		group by category.name
		order by count(ac.category_id) desc;
