<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
	        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
	        new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
	        new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
	        new JMS\AopBundle\JMSAopBundle(),
	        new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
	        new JMS\DiExtraBundle\JMSDiExtraBundle($this),

	        new Pms\CoreBundle\PmsCoreBundle(),
	        new Pms\UserBundle\UserBundle(),
            new Slik\DompdfBundle\SlikDompdfBundle(),
            new Pms\PettyCashBundle\PmsPettyCashBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Pms\InventoryBundle\PmsInventoryBundle(),
            new Xiidea\EasyMenuAclBundle\XiideaEasyMenuAclBundle(),
            new Pms\SettingBundle\PmsSettingBundle(),
            new Pms\BudgetBundle\PmsBudgetBundle(),
            new Pms\ReportBundle\PmsReportBundle(),
            new Pms\DocumentBundle\PmsDocumentBundle(),
            new EightPoints\Bundle\GuzzleBundle\GuzzleBundle(),
            new Pms\SupplierBundle\PmsSupplierBundle(),
            new Xiidea\EasyFormBundle\XiideaEasyFormBundle(),
            new Dizda\CloudBackupBundle\DizdaCloudBackupBundle(),
            new SunCat\MobileDetectBundle\MobileDetectBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
