<?php
/**
 * What The Email
 *
 * Parse that email and tell me what it is.
 *
 * @author       Daniel Bachhuber <d@danielbachhuber.com>
 */

class What_The_Email
{
	private $email;

	/**
	 * @param object      $email     An email, as provided by imap_fetchheaders() and imap_fetchbody()
	 */
	function __construct( $email ) {

		$this->email = $email;
	}

	/**
	 * Methods you can use
	 */

	/**
	 * Use regex to get the reply body of an email
	 */
	public function get_reply() {

		$body = $this->email->body;

		foreach( $this->email_body_regex as $pattern ) {
			if ( preg_match( '/^((.|\n)*)' . $pattern . '/iU', $body, $matches ) ) {
				return trim( $matches[1] );
			}
		}
		return $body;
	}

	/**
	 * Based on one of the fields, whether or not this email is likely a robot
	 *
	 * @return bool $block_it Whether or not the email is likely a robot
	 */
	public function is_robot() {

		$fields = array(
				'sender',
				'subject',
				'message'
			);

		foreach( $fields as $field ) {

			$search = 'blocked_' . $field . 's';
			if ( ! isset( $this->$search ) )
				continue;

			foreach( $this->$search as $trigger ) {

				// @todo add getter methods for reach field
				$value = '';
				if ( false !== stripos( $value, $trigger ) )
					return true;
			}
		}

		return false;
	}

	/**
	 * Strings for matching
	 */

	public $blocked_senders = array(
			'twitter-invite-comment',
			'notifications@academia.edu',
			'invitations@boxbe.com',
			'noreply-tkmailias@dot.tk',
			'no-reply@evernote.com',
			'invite@facebookmail.com',
			'update@facebookmail.com',
			'@friendfeed.com',
			'@plus.google.com',
			'@info-emailer.com',
			'@infoaxe.net',
			'invitations@linkedin.com',
			'@bounce.linkedin.com',
			'intlspoof3@paypal.com',
			'postmaster@',
			'@twoomail.com',
			'webmaster',
			'uservoice.com',
			'@zeekler.com',
			// Common noreply
			'donotreply@',
			'nobody@',
			'no-reply@',
			'noreply@',
		);

	public $blocked_subjects = array(
			'Auto Response',
			'auto-response',
			'Auto Respond',
			'out of the office',
			'out of office',
			'Outofoffice',
			'away from office',
			'Away from the office',
			'Out on Maternity Leave',
			'AUTOREPLY',
			'Auto-Reply',
			'Out-of-Office Reply',
			'My email address has changed',
			// Italian
			'Risposta automatica',
			'Autoresponder',
			// Spanish
			'Respuesta automática',
			'Cambio de direccion de correo',
			'Fuera de oficina',
			// Portugese,
			'Auto-Resposta',
			'Resposta automática',
			'Periodo de vacaciones',
			// German
			'Abwesenheitsnotiz',
			'Automatische Antwort',
			// French
			'Absente du bureau',
			'Răspuns automat',
			// Vietnamese
			'Tự động trả lời',
			// Indonesian
			'Jawaban Otomatis',
		);

	public $blocked_messages = array(
			'Having trouble viewing this email?',
			'autoreply',
			'auto reply',
			'This is an automated response',
			'This is an automated message',
			'This is an automatically generated email',
			'This is just Auto Response from Yahoo',
			'This email address is no longer valid',
			'ChoiceMail is holding the message you sent because your email address is not on my list of approved senders',
			'Because of the large amount of junk email I receive, I use ChoiceMail One to protect my Inbox',
			'The person you tried to send this email to is using SpamBLK Whitemail to prevent spam.',
			'I listened to your email using DriveCarefully',
			'Get your message to my inbox by clicking the following link.',
			'Please add yourself to my Boxbe Guest List so your messages will
go to my Inbox.',
			'Your message has been discarded',
			'Your email has been forwarded',
			'Your email has been automatically forwarded',
			'Your email will be automatically forwarded',
			'Return Receipt',
			"I'm out of office",
			"I'm out of the office",
			"I'm currently out of the office",
			'I am out of office',
			'I am out of the office',
			'I am currently out of office',
			'I am currently out of the office',
			'I am away from the office',
			'I am now out of the office',
			'I am not in the office',
			"I'll be out of the office",
			'I will be out of office',
			'I will be out of the office',
			'I will be on leave',
			'I will be back in the office',
			'I will not be in the office',
			'is out of the office',
			"We'll be out of the office",
			'We will be out of the office',
			'Our office will remain closed',
			'I am currently on annual leave',
			'I am currently on maternity leave',
			'will respond to your email when I return',
			'we will respond to you as soon as possible',
			'I will be in touch shortly',
			'Thank you for contacting',
			'Thank you for your email',
			'Thank for your e-mail',
			'Thank you for your enquiry',
			'Thanks for your enquiry',
			'Thank you for your inquiry',
			'Thank you for your query',
			'Thanks for the message',
			'Thank you for your message',
			'Thanks for your info',
			'Sorry. Your message could not be delivered to:',
			'I am currently unable to take your message',
			'Please note that this email address has now changed',
			// Spanish
			'Estaré fuera de la oficina',
			'Estoy de vacaciones',
			'Esta dirección de correo ya no es válida',
			'me encuentro ausente de la oficina',
			'Gracias por habernos contacto',
			'Gracias por contactar',
			'Su mensaje ha sido recibido correctamente',
			// French
			'Je serai absent(e)',
			// Portugese
			'Esatrei ausente até',
			'Agradecemos o seu contacto',
			// Dutch
			'bedankt voor uw mail',
			'Bedankt voor uw e-mail',
			// Turkish
			'Mailiniz ulaşmıştır ilginiz için teşekkür ederiz.',
		);

	public $email_body_regex = array(
			// On Saturday, October 27, 2012 at 3:28 PM, Testing wrote:
			'(On|At|In|Il|Em|Pada|Le|Am){1}\s.+\n?.+(wrote|writes|scritto|escreveu|menulis|écrit|(schrieb.+)){1}\s?:',
		);

}
