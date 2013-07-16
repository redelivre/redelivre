<?php

if ( isset($_GET['prefix']) && is_string($_GET['prefix']) && preg_match('/^[a-zA-Z0-9]{15,17}$/',$_GET['prefix']) ){   
   // no session
   $prefix = $_GET['prefix'];

   include 'securimage.php';

   $char_length = 4;
   $chars = 'ABCDEFHKLMNPRSTUVWYZ234578';
   $chars_num = '234578'; // do not change this or the code will break!!
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

   $img = new securimage_si();

   $img->image_width   = 175;
   $img->image_height  = 60;

   if(isset($_GET['si_sm_captcha']) && $_GET['si_sm_captcha'] == 1) {
       $img->image_width   = 132;
	   $img->image_height  = 45;
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

   $img->multi_text_color = array(
       '#6666FF','#660000','#3333CC','#993300','#0060CC',
       '#339900','#6633CC','#330000','#006666','#CC3366',
   );

   $img->charset = 'ABCDEFHKLMNPRSTUVWYZ234578';
   $img->code_length = 4;
   $img->ttf_file = $img->working_directory  . '/ttffonts/ahg-bold.ttf';   // single font
   $img->line_color = new Securimage_Color_si(rand(0, 64), rand(64, 128), rand(128, 255));
   $img->num_lines = 6;
   $img->perturbation = 0.75; // 1.0 = high distortion, higher numbers = more distortion
   $img->image_type = 'png';
   $img->background_directory = $img->working_directory . '/backgrounds';
   $img->ttf_font_directory  = $img->working_directory . '/ttffonts';
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

   $img = new securimage_si();

   $img->image_width   = 175;
   $img->image_height  = 60;

   if(isset($_GET['si_sm_captcha']) && $_GET['si_sm_captcha'] == 1) {
       $img->image_width   = 132;
	   $img->image_height  = 45;
   }

   //set some settings
   if( isset($_GET['si_form_id']) && in_array($_GET['si_form_id'], array('com', 'reg', 'log')) ) {
            $img->form_id = $_GET['si_form_id'];
   }

   $img->multi_text_color = array(
       '#6666FF','#660000','#3333CC','#993300','#0060CC',
       '#339900','#6633CC','#330000','#006666','#CC3366',
   );

   $img->charset = 'ABCDEFHKLMNPRSTUVWYZ234578';
   $img->code_length = 4;
   $img->ttf_file = $img->working_directory . '/ttffonts/ahg-bold.ttf';   // single font
   $img->line_color = new Securimage_Color_si(rand(0, 64), rand(64, 128), rand(128, 255));
   $img->num_lines = 6;
   $img->perturbation = 0.75; // 1.0 = high distortion, higher numbers = more distortion
   $img->image_type = 'png';
   $img->background_directory = $img->working_directory . '/backgrounds';
   $img->ttf_font_directory  = $img->working_directory . '/ttffonts';
   $img->show('');

   unset($img);
   exit;
}

?>