<?php

namespace Pms\ReportBundle\Service;


class EasyMailer
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer) {

        $this->mailer = $mailer;
    }

    public function send($to, $from, $subject, $body, $attachments = array())
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body,'text/html');
        $sent = $this->mailer->send($message);
        var_dump($sent);
    }
}