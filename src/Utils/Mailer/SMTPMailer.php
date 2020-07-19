<?php
namespace Nish\Utils\Mailer;


use Nish\PrimitiveBeast;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class SMTPMailer extends PrimitiveBeast implements IMailer
{
    public static function sendSMTPMail($host, $username, $password, ?array $to = null, ?array $bcc = null, ?array $cc = null, $fromAddr = null, $subject = '', $htmlBody = '', $textBody = '', $port = 587, $smtpSecure = 'tls', $replyTo = null, ?array $attachments = null)
    {
        $mailer = new PHPMailer(true);
        $mailer->isHTML(true);

        if (self::isAppInDebugMode()) {
            $mailer->SMTPDebug = SMTP::DEBUG_SERVER;

            $mailer->Debugoutput = function ($message, $debugLevel) {
                $logger = self::getDefaultLogger();

                $logger->debug($message);
            };
        } else {
            $mailer->SMTPDebug = SMTP::DEBUG_OFF;
            $mailer->Debugoutput;
        }


        $mailer->SMTPAuth = true;
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->Username = $username;
        $mailer->Password = $password;

        if ($smtpSecure == 'tls') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($smtpSecure == 'ssl') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        if ($fromAddr) {
            $mailer->setFrom($fromAddr);
        }

        if ($replyTo) {
            $mailer->addReplyTo($replyTo);
        }

        if (!empty($to)) {
            foreach ($to as $addr) {
                $mailer->addAddress($addr);
            }
        }

        if (!empty($bcc)) {
            foreach ($bcc as $addr) {
                $mailer->addBCC($addr);
            }
        }

        if (!empty($cc)) {
            foreach ($cc as $addr) {
                $mailer->addCC($addr);
            }
        }

        if (empty($subject)) {
            $subject = 'Untitled';
        }

        $mailer->Subject = $subject;

        $mailer->Body = $htmlBody;

        if ($textBody) {
            $mailer->AltBody = $textBody;
        }

        if (!empty($attachments)) {
            foreach ($attachments as $i => $v) {
                if (is_numeric($i)) {
                    $mailer->addAttachment($v);
                } else {
                    $mailer->addAttachment($v, $i);
                }
            }
        }

        $mailer->send();
    }
}