jQuery(function($){

	$(window).on('resize', function(){
		if ($(this).width() > 600) {
			$('.menu-tmp').remove();
		}
	});

	var liParent = $('.menu li > ul').parent('li');

	liParent.children('a').append(' &raquo;')

	liParent.each(function(){
		$(this).children('a').on('click', function(){
			if($(window).width() < 600) {
				var liLinkOriginalHTML = '<a href="'+$(this).attr('href')+'">'+$(this).text().replace($(this).text().substr(-1), '')+'</a>';
				var ulChildren = $(this).next('ul');
				var content = $(this).parent('li').children('a').next().html();

				$('.menu').fadeOut('fast');
				$('.menu-main-container').append('<ul class="menu-tmp"><li><a href="#" class="menu-back-live">&laquo; Voltar</a>'+liLinkOriginalHTML+'</li>'+content+'</ul>');

				return false;
			}
		});
	});

	$('.menu-back-live').live('click', function(){
		if($(window).width() < 600) {
			$('.menu-tmp').fadeOut('fast');
			$('.menu-tmp').remove();

			$('.menu').fadeIn();

			return false;
		}
	});

	$('.menu-toggle').live('click', function(event){
		if($(window).width() < 600) {
			if($('.menu-tmp').length > 0) {
				$('.menu-tmp').remove();
			}
		}
	});
});
