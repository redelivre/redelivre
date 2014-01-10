jQuery(document).ready(function() {
	var $ptemplate_select = jQuery('select#page_template'),
		$ptemplate_box = jQuery('#et_ptemplate_meta');

	$ptemplate_select.live('change',function(){
		var this_value = jQuery(this).val();
		$ptemplate_box.find('.inside > div').css('display','none');

		switch ( this_value ) {
			case 'page-sitemap.php':
				$ptemplate_box.find('.et_pt_sitemap').css('display','block')
				break;
			case 'page-blog.php':
				$ptemplate_box.find('.et_pt_blog').css('display','block')
				break;
			case 'page-gallery.php':
				$ptemplate_box.find('.et_pt_gallery').css('display','block')
				break;
			case 'page-template-portfolio.php':
				$ptemplate_box.find('.et_pt_portfolio').css('display','block')
				break;
			case 'page-search.php':
				$ptemplate_box.find('.et_pt_search').css('display','block')
				break;
			case 'page-login.php':
				$ptemplate_box.find('.et_pt_login').css('display','block')
				break;
			case 'page-contact.php':
				$ptemplate_box.find('.et_pt_contact').css('display','block')
				break;
			default:
                $ptemplate_box.find('.et_pt_info').css('display','block');
		}
	});

	$ptemplate_select.trigger('change');
});