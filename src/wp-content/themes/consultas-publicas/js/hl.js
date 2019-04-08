var hl = {
	lightbox: {
		close_button_label: 'x',
		darkbox_css: {opacity: .8},
		lightbox_css: {opacity: 1},
		darkbox: null,
		lightbox: null,
		
		init: function (){
			// cria o darkbox ( div que deixa o fundo escuro )
			if(jQuery('.hl-darkbox').length == 0){
				jQuery(document.body).append("<div class='hl-darkbox'></div>");
				this.darkbox = jQuery('.hl-darkbox');
				this.darkbox.click(function(){
					hl.lightbox.close();
				});
			}
			
			jQuery('.hl-lightbox').each(function(){
				jQuery(this).data('dialog', jQuery(this).find('.hl-lightbox-dialog'));
				
			});
			
			jQuery('.hl-lightbox-dialog').each(function(){
				jQuery(this).hide();
				jQuery(document.body).append(this);
				
				jQuery(this).find('.hl-lightbox-close').click(function(){
					hl.lightbox.close();
					return false;
				});
			});
			
			jQuery('.hl-lightbox').click(function(){
				var dialog = jQuery(this).data('dialog');
				hl.lightbox.open(dialog);
				return false;
			});
			
		},
		
		open: function(div){
			this.lightbox = div;
			
			var _width = parseInt(div.css('width'));
			var _height = parseInt(div.css('height'));
			
			var _left = (parseInt(jQuery(window).width()/2 - _width/2))+'px';
			var _top = (parseInt(jQuery(window).height()/2 - _height/2) + jQuery(window).scrollTop())+'px'; 
			
			this.darkbox.css({opacity: 0, position:'absolute', top:'0px', left:'0px', zIndex: '9998', width: '100%', height: jQuery(document).height()+'px'}).show().animate(this.darkbox_css);
			div.css({opacity: 0, position:'absolute', left: _left, top: _top, zIndex: '9999'}).show().animate(this.lightbox_css,'fast');
			jQuery(document.body).css('overflow', 'hidden');
		},
		
		close: function(){
			this.darkbox.hide();
			this.lightbox.hide();
			jQuery(document.body).css('overflow', 'auto');
		}
	}
	
};

jQuery(document).ready(function(){
	
	hl.lightbox.init();
	
});
