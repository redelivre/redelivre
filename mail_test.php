<?php

include_once 'src/wp-includes/class-phpmailer.php';

$phpmailer = new PHPMailer(true);

$phpmailer->AddReplyTo('jacson@passold.net.br');

$phpmailer->From     = "test@redelivre.org";
$phpmailer->FromName = "Test";

$phpmailer->AddAddress('jacson@ethymos.com.br', "Jacson");

$phpmailer->Subject = 'Test 123';
$phpmailer->Body    = 'Testando 123';

$phpmailer->IsMail();

$phpmailer->ContentType = 'text/plain';

$phpmailer->CharSet = 'UTF8';

$phpmailer->

try {
	$phpmailer->Send();
} catch ( phpmailerException $e ) {
	echo '<pre>Error\n';
	var_dump($e);
	echo '</pre>';
}

echo 'OK';

?>