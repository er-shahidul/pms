<?php

namespace Pms\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pms\UserBundle\DependencyInjection\Compiler\PermissionCompilerPass;

class UserBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		$container->addCompilerPass(new PermissionCompilerPass());
	}

    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
