<?php

namespace Pms\CoreBundle\Services;

use IDCI\Bundle\NotificationBundle\Entity\Notification;
use IDCI\Bundle\NotificationBundle\Notifier\EmailNotifier as BaseNotifier;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailNotifier extends BaseNotifier implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function sendNotification(Notification $notification)
    {
        $content = json_decode($notification->getContent(), true);

        $message = \Swift_Message::newInstance()
            ->setSubject($content['subject'])
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($notification->getTo())
            ->setBody($content['message'])
        ;

        if ($content['htmlMessage']) {
            $message->addPart($content['htmlMessage'], 'text/html');
        }

        $this->getMailer()->send($message);
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = NULL)
    {
        $this->container = $container;
    }
}