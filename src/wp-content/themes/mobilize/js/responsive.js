jQuery(function($){

	function mobilizeResponsive()
	{
		if ($(window).width() <= 768) {
			$('.social').parent().removeClass('right');
			$('#social-bookmarks').removeClass('alignright');
			$('.brand').css('margin-left', 0);
		}

		if ($(window).width() > 768) {
			$('.social').parent().addClass('right');
			$('#social-bookmarks').addClass('alignright');
			$('.brand').css('margin-left', '30px');
		}
	}

	mobilizeResponsive();

	$(window).on('resize', function(){
		if ($(this).width() > 600) {
			$('ul.menu-tmp').remove();
			$('ul.menu').show();
		}

		///////////////////////////////////////////////////
		// Alterações Exclusivas para o Tema Mobilize //
		///////////////////////////////////////////////////

		mobilizeResponsive();
	});

	function makeMenuResponsive(liParent)
	{
		liParent.each(function(){
			$(this).children('a').on('click', function(){
				if($(window).width() < 768) {
					var liLinkOriginalHTML = '<a href="'+$(this).attr('href')+'">'
						+$(this).text().replace($(this).text().substr(-1), '')+'</a>';

					var ulChildren = $(this).next('ul');
					var content = $(this).parent('li').children('a').next().html();

					$('ul.menu').hide();
					$('.menu-main-container').append('<ul class="menu menu-tmp"><li>'
						+'<a href="#" class="menu-back-live">&laquo; Voltar</a></li><li>'
						+liLinkOriginalHTML+'</li>'+content+'</ul>');

					makeMenuResponsive($('ul.menu-tmp li > ul').parent('li'));

					return false;
				}
			});
		});
	}

	var liParent = $('ul.menu li > ul').parent('li');
	liParent.children('a').append(' &raquo;');
	makeMenuResponsive(liParent);

	$('.menu-back-live').live('click', function(){
		if($(window).width() < 768) {
			var menu = $(this).parents('.menu-tmp');
			var oldMenu = menu.prev();
			menu.remove();
			oldMenu.show();

			return false;
		}
	});

	$('.menu-toggle').live('click', function(event){
		if($(window).width() < 768) {
			if($('.menu-tmp').length > 0) {
				$('.menu-tmp').remove();
			}
		}
	});
});
