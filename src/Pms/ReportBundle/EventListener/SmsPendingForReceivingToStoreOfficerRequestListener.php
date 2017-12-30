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


class SmsPendingForReceivingToStoreOfficerRequestListener extends SendSmsAwareListener
{

    public function sendSms($event)
    {

        $msg = "Item for po no 007 is waiting for receiving in your store";
        $this->gateway->send($msg, '+8801917147677');

    }
}