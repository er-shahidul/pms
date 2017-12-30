<?php

namespace Pms\CoreBundle\Controller;

use Doctrine\ORM\Repository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Core controller.
 *
 */
class CoreController extends Controller
{
    public function renderLogoAction()
    {
        $companySettingQuery    = $this->getDoctrine()->getRepository('PmsSettingBundle:CompanySetting')
            ->createQueryBuilder('c')
            ->where('c.status = 1');
        $companySetting = $companySettingQuery->getQuery()->getResult();

        if(!$companySetting){
            $valLogo = 1;

            return $this->render('::partials/default.html.twig', array(
                'valLogo' => $valLogo,
            ));

        }else{
            $logo = $companySetting[0]->getPath();
            if($logo != null){
                $valLogo = 0;
            }else{
                $valLogo = 1;
            }
            return $this->render('::partials/logo.html.twig', array(
                'logo' => $logo,
                'valLogo' => $valLogo,
            ));
        }
    }

    public function renderTitleAction()
    {
        $companySettingQuery    = $this->getDoctrine()->getRepository('PmsSettingBundle:CompanySetting')
            ->createQueryBuilder('c')
            ->where('c.status = 1');
        $companySetting = $companySettingQuery->getQuery()->getResult();

        if(!$companySetting){
            return $this->render('::partials/default.html.twig', array(
            ));
        }else{
            $title = $companySetting[0]->getName();
            $details = $companySetting[0]->getDetails();

            return $this->render('::partials/title.html.twig', array(
                'title' => $title,
                'details' => $details,
            ));
        }
    }

    public function backupDatabaseAction(){

        /*$databaseBackup = $this->get('pms_core.database_backup');
        $databaseBackup->databaseBackUp();*/
        $command = 'php /var/www/html/purchase/app/console dizda:backup:start --env=prod';
        shell_exec($command);

        return $this->redirect($this->generateUrl('homepage'));
    }
}
