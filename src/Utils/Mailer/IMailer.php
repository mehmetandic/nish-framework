<?php
namespace Nish\Utils\Mailer;


interface IMailer
{
    public static function sendSMTPMail($host, $username, $password, ?array $to, ?array $bcc, ?array $cc, $fromAddr, $subject, $htmlBody, $textBody, $port, $smtpSecure, $replyTo, ?array $attachments);
}