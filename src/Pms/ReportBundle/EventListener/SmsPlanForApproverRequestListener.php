<?php
/**
 * Created by PhpStorm.
 * User: dhaka
 * Date: 8/19/14
 * Time: 4:51 PM
 */

namespace Pms\ReportBundle\EventListener;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Pms\ReportBundle\Event\PmsSmsRequestEvent;
use Pms\ReportBundle\Service\SmsGateWay;
use Pms\UserBundle\Entity\User;


class SmsPlanForApproverRequestListener extends SendSmsAwareListener
{

    public function sendSms($event)
    {

        $this->gateway->send($event->smsRequest['prno'], $event->smsRequest['cellphone']);
    }
}