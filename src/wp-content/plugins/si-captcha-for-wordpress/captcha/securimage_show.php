<?php
//error_reporting(E_ALL); // Report all errors and warnings (very strict, use for testing only)
//ini_set('display_errors', 1); // turn error reporting on
//ini_set('log_errors', 1); // log errors
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); // where to log errors

if ( isset($_GET['prefix']) && is_string($_GET['prefix']) && preg_match('/^[a-zA-Z0-9]{15,17}$/',$_GET['prefix']) ){   
   // no session
   $prefix = $_GET['prefix'];

   include 'securimage.php';

   $char_length = 4;
   $chars = 'ABCDEFGHKLMNPRSTUVWYZabcdeghmnpsuvwyz23456789';
   $chars_num = '2345789'; // do not change this or the code will break!!
   // one random position always has to be a number so that a 4 letter swear word could never appear
   $rand_pos = mt_rand( 0, $char_length - 1 );
   $captcha_word  = '';
   for ( $i = 0; $i < $char_length; $i++ ) {
       // this rand character position is a number only so that a 4 letter swear word could never appear
       if($i == $rand_pos) {
              $pos = mt_rand( 0, strlen( $chars_num ) - 1 );
              $char = $chars_num[$pos];
       } else {
              $pos = mt_rand( 0, strlen( $chars ) - 1 );
              $char = $chars[$pos];
       }
	   $captcha_word .= $char;
   }

   $img = new Securimage_Captcha_si();
   $img->image_width   = 175;
   $img->image_height  = 60;
   $img->num_lines     = 3;
   $img->perturbation  = 0.2;
   $img->iscale        = 1;
   if(isset($_GET['si_sm_captcha']) && $_GET['si_sm_captcha'] == 1) {
       $img->image_width   = 132;
	   $img->image_height  = 45;
       $img->num_lines     = 1;
   }

   //set some settings
   $img->nosession = true;
   $img->prefix = $prefix;
   $img->captcha_path = $img->working_directory . '/cache/';
   if(file_exists($img->captcha_path . $prefix . '.php') && is_readable( $img->captcha_path . $prefix . '.php' ) ) {
       include( $img->captcha_path . $prefix . '.php' );
       $img->captcha_word = $captcha_word;
   } else {
       $img->captcha_word = $captcha_word;
   }

   $img->line_color = new Securimage_Color_si(rand(0, 64), rand(64, 128), rand(128, 255));

   $img->show('');
   if(!file_exists($img->captcha_path . $prefix . '.php')) {
      $img->clean_temp_dir( $img->captcha_path );
      if ( $fh = fopen( $img->captcha_path . $prefix . '.php', 'w' ) ) {
			fwrite( $fh, '<?php $captcha_word = \'' . $captcha_word . '\'; ?>' );
			fclose( $fh );
            @chmod( $img->captcha_path . $prefix . '.php', 0755 );
      }
   }
   unset($img);
   exit;
} else {
       // session
   include 'securimage.php';

   $img = new Securimage_Captcha_si();
   $img->image_width   = 175;
   $img->image_height  = 60;
   $img->num_lines  = 3;
   $img->perturbation  = 0.2;
   $img->iscale        = 1;
   //$img->code_length   = 4;
   //$img->charset   = 'ABCDEFGHKLMNPRSTUVWYZabcdeghmnpsuvwyz23456789';
   if(isset($_GET['si_sm_captcha']) && $_GET['si_sm_captcha'] == 1) {
       $img->image_width   = 132;
	   $img->image_height  = 45;
       $img->num_lines     = 1;
   }

   //set some settings
   if( isset($_GET['si_form_id']) && in_array($_GET['si_form_id'], array('com', 'reg', 'log')) ) {
            $img->form_id = $_GET['si_form_id'];
   }

   $img->line_color = new Securimage_Color_si(rand(0, 64), rand(64, 128), rand(128, 255));

   $img->show('');

   unset($img);
   exit;
}

// end of file
