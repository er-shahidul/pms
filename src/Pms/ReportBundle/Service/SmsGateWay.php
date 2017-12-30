<?php

namespace Pms\ReportBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Pms\ReportBundle\Event\PmsSmsRequestEvent;
use Pms\ReportBundle\EventListener\SendSmsAwareListener;

class SmsGateWay
{

    private $username;
    private $password;
    /**
     * @var Client
     */
    private $client;

    public function  __construct($username, $password, Client $client)
    {

        $this->username = $username;
        $this->password = $password;
        $this->client = $client;
    }


    function send($msg, $phone){

        try {

            $body = '{"authentication": {"username": "' . $this->username .'","password": "'.$this->password.'"},"messages": [{"sender": "NPMS","text": "'.$msg.'","recipients": [{"gsm": "'.$phone.'"}]}]}';

            $response = $this->client->post(
                "/api/v3/sendsms/json",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept'       => '/',
                    ],
                    'body'    => $body,
                ]
            );
            $content  = $response->getBody()->getContents();
            var_dump($content);
        } catch (RequestException $e) {
            var_dump($e->getRequest());
            if ($e->hasResponse()) {
                var_dump($e->getResponse()->getReasonPhrase());
            }
        }

    }
}