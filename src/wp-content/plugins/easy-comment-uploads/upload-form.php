<?php require ('../../../wp-blog-header.php'); ?>
<!doctype html>
<?php	echo '<link rel="stylesheet" href="' .get_bloginfo( 'template_url' ) . '/style-upload.css" type="text/css" media="all" />';?>
<body style="color:#fff; background : transparent; font-family : 'Lucida Grande', Verdana, Arial, sans-serif; font-size : 10pt">
        <?php ecu_upload_form_core (); ?>
</body>
