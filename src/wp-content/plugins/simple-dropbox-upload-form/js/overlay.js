(function($) {
	/* openoverlay v4.9 */
	var $w,$d;
	$.fn.openOverlay = function(options) {
		$w= $(window),$d = $(document)
		var opts = $.extend({}, $.fn.openOverlay.defaults, options),t=0;
		if (options && (options === 'close' || options.close)){
			$.fn.openOverlay.closeOverlay(opts.quickClose);
			return;
		}

		if (!$('#overlayLayer').length){
			var occ,i,iLength,
			h = $w.height(),$ol,
			$docbody = $(document.body);  
			if (!h) h=100000;
			$ol = $('<div id="overlayLayer" style="'+
				'text-align:center; z-index:999999; width:100%; position:absolute; top:0px; bottom:0px; left:0px; right:0px;'+
				' background:'+opts.sColor+';"></div>')
				.fadeTo(0,'0.' + opts.iOpacity)
				.appendTo($docbody);

			$w.bind('scroll.openOverlay',scrollResize)
				.bind('resize.openOverlay',scrollResize);
			$docbody.append('<div id="overlayContentContainer" style="float:left;  position:absolute; top:0; left:0;"></div>');

			if (opts.closeOnClick){
				setTimeout(function(){
					$ol.click(function(){
						$.fn.openOverlay.closeOverlay(opts.quickClose);
					});
				},1000)
			}
			fillrUp(1);
		}
		occ = $('#overlayContentContainer');
		iLength = this.length;
		return this.each(function(i) {
			var $b,$c,$this = $(this);
			/* enable content restoration */ 
			if(opts.restoreContent){
				$b = $('<span class="overlayPlaceHolder"></span>');
				$this.after($b);
				$c = $('<div class="overlayPlaceHolder"></div>');
				$c.append(this).data('overlayPlaceHolder',$b);
				occ.append($c);
			}else{
				occ.append(this);
			}

			if (i===iLength-1) positionOverlayContent();
		});

		function scrollResize(){
			if (t) clearTimeout(t);
			t = setTimeout(function(){
				fillrUp(opts.stickyContent)},100);				
		}
	};

	function positionOverlayContent(){
		var o = $('#overlayContentContainer'),
		h = $w.height(),
		ww = $w.width(),
		mtop= (h/2 - o.outerHeight()/2);
		if(!h) h=300;
		if(mtop < 0) mtop = 0; 
		o.css({
			marginTop: mtop + $w.scrollTop() + 'px',
			marginLeft: ww/2  - o.outerWidth()/2 + $w.scrollLeft()+'px',
			left:'0',position:'absolute',zIndex:'9999999'
		});
	};

	function fillrUp(stickyContent){
		var h = Math.max($d.height(),$w.height());
		$('#overlayLayer').css({height:h+'px'});
		if (stickyContent)
			positionOverlayContent();
	};

	$.fn.openOverlay.positionContent = positionOverlayContent;
	$.fn.openOverlay.closeOverlay = function(quickly){
		var $ov,$dt,
		$obj = $('#overlayLayer,#overlayContentContainer'),
		removeit = function(){
			$ov = $(this).find('.overlayPlaceHolder');
			if (this.id === 'overlayContentContainer' && $ov.length){
				$ov.each(function(){
					$dt = $(this).data('overlayPlaceHolder');
					if($dt){
						$dt.replaceWith(this.childNodes[0]);
					}else{
						$(this).remove();
					}
				});
			}
			$(this).remove();
		};

		$w.unbind('scroll.openOverlay')
			.unbind('resize.openOverlay');
		if (quickly){
			$obj.each(removeit);
		}else{
			$obj.fadeOut('fast',removeit);
		}
	};

	$.fn.openOverlay.defaults = {
			iOpacity:70,
			sColor:'#444444',
			restoreContent:false,
			closeOnClick:false,
			quickClose:true,
			stickyContent:false
	};
})(jQuery);