/*
Anti-spam plugin
No spam in comments. No captcha.
wordpress.org/plugins/anti-spam/
*/

(function($) {

	function anti_spam_init() {
		$('.comment-form-ant-spm, .comment-form-ant-spm-2').hide(); // hide inputs from users
		var answer = $('.comment-form-ant-spm input#ant-spm-a').val(); // get answer
		$('.comment-form-ant-spm input#ant-spm-q').val( answer ); // set answer into other input instead of user

		var current_date = new Date();
		var current_year = current_date.getFullYear();

		if ( $('#comments form input#ant-spm-q').length == 0 ) { // anti-spam input does not exist (could be because of cache or because theme does not use 'comment_form' action)
			$('#comments form').append('<input type="hidden" name="ant-spm-q" id="ant-spm-q" value="'+current_year+'" />'); // add whole input with answer via javascript to comment form
		}

		if ( $('#respond form input#ant-spm-q').length == 0 ) { // similar, just in case (used because user could bot have #comments)
			$('#respond form').append('<input type="hidden" name="ant-spm-q" id="ant-spm-q" value="'+current_year+'" />'); // add whole input with answer via javascript to comment form
		}

		if ( $('form#commentform input#ant-spm-q').length == 0 ) { // similar, just in case (used because user could bot have #respond)
			$('form#commentform').append('<input type="hidden" name="ant-spm-q" id="ant-spm-q" value="'+current_year+'" />'); // add whole input with answer via javascript to comment form
		}
	}

	$(document).ready(function() {
		anti_spam_init();
	});

	$(document).ajaxSuccess(function() { // add support for comments forms loaded via ajax
		anti_spam_init();
	});

})(jQuery);