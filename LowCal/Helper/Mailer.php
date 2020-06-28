<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Helper;

use PHPMailer\Exception;
use PHPMailer\PHPMailer;
use PHPMailer\SMTP;

/**
 * Class Mailer
 * @package LowCal\Helper
 */
class Mailer
{
	/**
	 * @param array $to
	 * @param string $subject
	 * @param string $html_message
	 * @param string $plain_message
	 * @param array $attachments
	 * @param string $replyto_email
	 * @param string $replyto_name
	 * @param string $from_email
	 * @param string $from_name
	 * @param array $cc
	 * @param array $bcc
	 * @return bool
	 * @throws Exception
	 */
	public static function sendMail(array $to, string $subject, string $html_message, string $plain_message = '', array $attachments = array(), string $replyto_email = '', $replyto_name = '', string $from_email = '', string $from_name = '', array $cc = array(), array $bcc = array()): bool
	{
		if(empty($to))
		{
			throw new Exception('No recipients provided.');
		}

		$mail = new PHPMailer(true);

		try
		{
			if(Config::get('APP_MAIL_ENABLE_SMTP'))
			{
				$mail->SMTPDebug = Config::get('APP_MAIL_ENABLE_SMTP_DEBUG_LEVEL');
				$mail->isSMTP();
				$mail->Host = Config::get('APP_MAIL_SMTP_HOST');
				$mail->SMTPAuth = Config::get('APP_MAIL_SMTP_ENABLE_AUTH');
				$mail->Username = Config::get('APP_MAIL_SMTP_USERNAME');
				$mail->Password = Config::get('APP_MAIL_SMTP_PASSWORD');
				$mail->SMTPSecure = Config::get('APP_MAIL_SMTP_ENCRYPTION_METHOD');
				$mail->Port = Config::get('APP_MAIL_SMTP_PORT');
			}

			$mail->setFrom(
				(!empty($from_email)?$from_email:Config::get('APP_MAIL_DEFAULT_FROM_EMAIL')),
				(!empty($from_name)?$from_name:Config::get('APP_MAIL_DEFAULT_FROM_NAME')),
				false
			);

			foreach($to as $email => $name)
			{
				$mail->addAddress($email, $name);
			}

			$mail->addReplyTo(
				(!empty($replyto_email)?$replyto_email:Config::get('APP_MAIL_DEFAULT_REPLYTO_EMAIL')),
				(!empty($replyto_name)?$replyto_name:Config::get('APP_MAIL_DEFAULT_REPLYTO_NAME'))
			);

			if(!empty($cc))
			{
				foreach($cc as $email => $name)
				{
					$mail->addCC($email, $name);
				}
			}
			elseif(!empty(Config::get('APP_MAIL_DEFAULT_CC_EMAILS')))
			{
				foreach(Config::get('APP_MAIL_DEFAULT_CC_EMAILS') as $email => $name)
				{
					$mail->addCC($email, $name);
				}
			}

			if(!empty($bcc))
			{
				foreach($bcc as $email => $name)
				{
					$mail->addBCC($email, $name);
				}
			}
			elseif(!empty(Config::get('APP_MAIL_DEFAULT_BCC_EMAILS')))
			{
				foreach(Config::get('APP_MAIL_DEFAULT_BCC_EMAILS') as $email => $name)
				{
					$mail->addBCC($email, $name);
				}
			}

			if(!empty($attachments))
			{
				foreach($attachments as $file_location => $attachment_name)
				{
					$mail->addAttachment($file_location, $attachment_name);
				}
			}

			$mail->Subject = $subject;

			if(!empty($html_message))
			{
				$mail->isHTML();
				$mail->Body = $html_message;
			}
			else
			{
				$mail->isHTML(false);
				$mail->Body = $plain_message;
			}

			$mail->AltBody = $plain_message;

			return $mail->send();
		}
		catch(Exception $e)
		{
			throw new Exception($mail->ErrorInfo);
		}
	}

	/**
	 * @param string $template_name
	 * @param array $parameters
	 * @param string $template_ext
	 * @return array
	 * @throws Exception
	 * @throws \Exception
	 */
	public static function renderTemplate(string $template_name, array $parameters = array(), string $template_ext = 'php'): array
	{
		global $LowCal;

		$mail_body = array(
			'html' => '',
			'plain' => ''
		);

		$file = Config::get('MAIL_TEMPLATES_DIR').$template_name.'.'.$template_ext;

		if(!file_exists($file))
		{
			throw new \Exception('Template could not be found.');
		}

		$parameters['LowCal'] = $LowCal;

		extract($parameters);

		try
		{
			ob_start();

			require $file;

			$content = ob_get_clean();

			ob_end_clean();

			$mail_body['html'] = $content;

			$mail_body['plain'] = strip_tags(str_replace(array('<br>','<br/>','<br />'), "\r\n", $content));
		}
		catch(\Throwable $t)
		{
			ob_end_clean();

			throw new \Exception($t->getMessage(), $t->getCode());
		}

		return $mail_body;
	}
}