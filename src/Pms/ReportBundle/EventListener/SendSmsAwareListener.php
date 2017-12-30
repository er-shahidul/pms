<?php
/**
 * Created by PhpStorm.
 * User: Bahar
 * Date: 4/9/15
 * Time: 5:05 PM
 */

namespace Pms\ReportBundle\EventListener;


use Pms\ReportBundle\Service\SmsGateWay;
use Symfony\Component\EventDispatcher\Event;

abstract class SendSmsAwareListener {

    /**
     * @var \Pms\ReportBundle\Service\SmsGateWay
     */
    protected $gateway;

    public function  __construct(SmsGateWay $gateway)
    {
        $this->gateway = $gateway;
    }

}