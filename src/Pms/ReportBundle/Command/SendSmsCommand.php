<?php

namespace Pms\ReportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * SendSmsCommand
 */
class SendSmsCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('pms:send-sms')
            ->setDescription('Send sms')
            ->setHelp(<<<EOT
                        The <info>pms:send-sms</info> command send sms:
                          <info>php app/console pms:send-sms</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $smsSender = $this->getContainer()->get('pms_reportbundle.service.smssender');


       $smsSender->smsPlanForApproverRequisitionAction();
       $smsSender->pendingForReceivingToStoreOfficerAction();
     //  $smsSender->sendPaymentVerification();
    //   $smsSender->smsPlanForDirectorMdFdAction();
       $output->writeln(sprintf('ooo <comment>sms sent</comment>'));
    }

}
