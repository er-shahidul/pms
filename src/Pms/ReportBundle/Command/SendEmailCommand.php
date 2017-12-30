<?php

namespace Pms\ReportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SendEmailCommand
 */
class SendEmailCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('pms:send-email')
            ->setDescription('Send email')
            ->setHelp(<<<EOT
                        The <info>pms:send-email</info> command send email:
                          <info>php app/console pms:send-email</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emailSender = $this->getContainer()->get('pms_reportbundle.service.emailsender');
//        $emailSender->sendEmail();
          $emailSender->prApprovalEmail();
          $emailSender->poApprovalEmail();
          $emailSender->receivePendingEmail();
          $emailSender->priClaimEmail();
          $emailSender->poPendingEmail();
        $output->writeln(sprintf('ooo <comment>email sent</comment>'));
    }
}
