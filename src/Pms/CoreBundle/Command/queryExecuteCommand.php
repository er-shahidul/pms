<?php

namespace Pms\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * queryExecuteCommand
 */
class queryExecuteCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('pms:query-execute')
            ->setDescription('execute query')
            ->setHelp(<<<EOT
                        The <info>pms:query-execute</info> command query execute for data:
                          <info>php app/console pms:query-execute</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updateData = $this->getContainer()->get('pms_core.service.query_service');


       // $updateData->purchaseRequisitionUpdateQuery();
        $updateData->purchaseOrderAmendmentUpdateQuery();

       $output->writeln(sprintf('ooo <comment>sms sent</comment>'));
    }

}
