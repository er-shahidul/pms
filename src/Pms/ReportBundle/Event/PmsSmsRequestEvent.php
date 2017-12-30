<?php
/**
 * Created by PhpStorm.
 * User: dhaka
 * Date: 8/19/14
 * Time: 5:24 PM
 */

namespace Pms\ReportBundle\Event;

use Pms\CoreBundle\Entity\PurchaseRequisition;
use Symfony\Component\EventDispatcher\Event;

class PmsSmsRequestEvent extends Event
{
    public $smsRequest;

    public function __construct($smsRequest)
    {
        $this->smsRequest = $smsRequest;
    }

    public function getRequest()
    {
        return $this->smsRequest;
    }


}