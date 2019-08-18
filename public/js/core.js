function Core() {
	this.srcUrl = 'src/';
	this.pageCount = 0;

	this.start = function() {
		var core = this;
		core.processUrl(core);
		core.defineBehaviour(core);
	};

	this.processUrl = function(core) {
		if (core.urlParam('page') !== undefined) {
			core.loadPage(core, core.urlParam('page'));
		} else {
			core.loadPage(core, 'blog');
		}
	};

	this.showError = function(err) {
		$('main').html('<p id="error-message">There is nothing in here.</p>');
		if (err !== undefined) {
			console.log(err);
		}
	};

	this.loadPage = function(core, page) {
		if (page === undefined) {
			var page = 'blog';
		}
		if (page == 'blog') {
			core.loadBlog(core, core.urlParam('filter'), core.urlParam('page-number'));
		} else {
			core.loadAjaxData(
					core,
					'json',
					'request.php',
					{
						'action': 'get-page',
						'page': page
					},
					core.appendMainContent);
			window.history.pushState(null, 'cdroot', '?page=' + page);
		}
		++core.pageCount;
	};

	this.appendMainContent = function(core, response) {
		if (response.state == 200) {
			$('main').html(response.body);
		} else {
			core.showError(response.body);
		}
	};

	this.loadBlog = function(core, filter, pageNumber) {
		core.loadAjaxData(
				core,
				'json',
				'request.php',
				{
					'action': 'get-page',
					'page': 'blog',
				},
				function(core, response) {
					core.appendMainContent(core, response);
					$('#page-current a').html(pageNumber);
					core.loadBlogEntries(core, filter, pageNumber);
				});
	};

	this.showEntries = function(entries) {
		var entriesHtml = '';
		entries.forEach(function(item) {
			entriesHtml = entriesHtml.concat('<tr><td id="' + item.name + '"><h3 class="blog-entry-title">' + item.title + '</h3><details><p>' + item.intro + '</p><ul class="list-inline">');
			item.categories.forEach(function(category) {
				entriesHtml = entriesHtml.concat('<li class="category-in-list category-' + category.name + '">' + category.name + '</li>');
			});
			entriesHtml = entriesHtml.concat('</ul></details></td></tr>');
		});
		$('#blog-entries').html(entriesHtml);
		$('.blog-entry-title').on('click', function() {
			core.loadPage(core, this.parentElement.id);
		});
	};

	this.pushBlogParams = function(filter, pageNumber) {
		var param = '?page=blog';
		if (filter !== undefined) {
			param = param.concat('&filter=' + filter);
		}
		if (pageNumber !== undefined) {
			param = param.concat('&page-number=' + pageNumber);
		}
		window.history.pushState(null, 'cdroot', param);
	}

	this.loadBlogEntries = function(core, filter, pageNumber) {
		core.loadAjaxData(
				core,
				'json',
				'request.php',
				{
					'action': 'get-blog-entries',
					'filter': filter,
					'page-number': pageNumber
				},
				function(core, response) {
					if (response.state == 200) {
						core.showEntries(response.body);
					} else {
						core.showError(response.body);
					}
				});
		core.pushBlogParams(filter, pageNumber);
	};

	this.nextPage = function(core, filter) {
		var pageNumber = Number($('#page-current a').html()) + 1;
		core.loadAjaxData(
				core,
				'json',
				'request.php',
				{
					'action': 'get-blog-entries',
					'filter': filter,
					'page-number': pageNumber
				},
				function(core, response) {
					if (response.state === 200) {
						if (response.body.length === 0) {
							return;
						}
						core.showEntries(response.body);
						$('#page-current a').html(pageNumber);
						core.pushBlogParams(filter, pageNumber);
					} else {
						core.showError(response.body);
					}
				});
	};

	this.prevPage = function(core, filter) {
		var pageNumber = Number($('#page-current a').html()) - 1;
		if (pageNumber < 1) {
			return;
		}
		core.loadAjaxData(
				core,
				'json',
				'request.php',
				{
					'action': 'get-blog-entries',
					'filter': filter,
					'page-number': pageNumber
				},
				function(core, response) {
					if (response.state === 200) {
						if (response.body.length === 0) {
							return;
						}
						core.showEntries(response.body);
						$('#page-current a').html(pageNumber);
						core.pushBlogParams(filter, pageNumber);
					} else {
						core.showError(response.body);
					}
				});
	};

	this.makeBlogEntriesFilter = function(core, removedCategory) {
		core.activateUrlCategories(removedCategory);
		var filter = [];
		$('#category-list .active').each(function(index) {
			filter.push($(this).attr('id'));
		});
		if (filter.length > 0) {
			return filter.join();
		}
		return undefined;
	};

	this.activateUrlCategories = function(removedCategory) {
		var filter = core.urlParam('filter');
		if (filter !== undefined) {
			filter = filter.split(',');
			if (removedCategory !== undefined) {
				var index = filter.indexOf(removedCategory);
				filter.splice(index, 1);
			}
			$('#category-list .category').each(function(item) {
				if (filter.indexOf(this.id) !== -1) {
					$(this).addClass('active');
				}
			});
		}
	};

	this.loadCategories = function(core) {
		core.loadAjaxData(
				core,
				'json',
				'request.php',
				{
					'action': 'get-categories',
				},
				function(core, response) {
					if (response.state == 200) {
						var categories = '';
						response.body.forEach(function(item) {
							categories = categories.concat('<li id="' + item.name + '" class="list-group-item clickable category category-' + item.name + '">' + item.name + '</li>');
						});
						$('#category-list').html(categories);
						core.activateUrlCategories();		
						$('#category-list .category').on('click', function() {
							var removedCategory = undefined;
							if ($(this).hasClass('active')) {
								$(this).removeClass('active');
								removedCategory = this.id;
							} else {
								$(this).addClass('active');
							}
							$('#page-current a').html(1);
							core.loadBlogEntries(core, core.makeBlogEntriesFilter(core, removedCategory));
						});
					} else {
						console.log(response.body);
					}
				});
	};

	this.defineBehaviour = function(core) {
		$("#blog").on("click", function() {
			window.history.pushState(null, 'cdroot', '?page=blog');
			core.loadPage(core, 'blog');
			$('.navbar-collapse').collapse('hide');
		});
		$("#about").on("click",function() {
			core.loadPage(core, 'about');
			$('.navbar-collapse').collapse('hide');
		});
		$("#links").on("click",function() {
			core.loadPage(core, 'links');
			$('.navbar-collapse').collapse('hide');
		});
		$(window).on('popstate', function(e) {
			core.pageCount = Math.max(1, core.pageCount);
			--core.pageCount;
			if (core.pageCount === 0) {
				window.history.go(-1);
			} else {
				core.processUrl(core);
				--core.pageCount;
			}
		});
	};

	this.urlParam = function(param) {
		var value = new RegExp('[\?&]' + param + '=([^&#]*)').exec(window.location.href);
		if (value === null) {
			return undefined;
		}
		return decodeURIComponent(value[1]);
	};

	this.loadAjaxData = function(core, responseType, url, params, callbackFunc, callbackParams) {
		var resUrl = core.srcUrl + url;
		$.ajax({
			url: resUrl,
			type: 'GET',
			dataType: responseType,
			data: params,
			async: true,
			success: function(response) {
				if (typeof callbackFunc !== 'undefined') {
					if (typeof callbackParams === 'undefined') {
						callbackFunc(core, response);
					} else {
						callbackFunc(core, response, callbackParams);
					}
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				if (textStatus !== 'abort') {
					var msg = 'Oops!\nSomething went wrong with the application (error: "' + errorThrown + '" on url: "' + resUrl + '")';
					console.log(msg);
				}
			}
		});
	};
};
