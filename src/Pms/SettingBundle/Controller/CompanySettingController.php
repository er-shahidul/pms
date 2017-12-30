<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\CompanySetting;
use Pms\SettingBundle\Form\CompanySettingType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Company Setting controller.
 *
 */
class CompanySettingController extends Controller
{
    public function indexAction(Request $request)
    {
        $companySettingQuery    = $this->getDoctrine()->getRepository('PmsSettingBundle:CompanySetting')
            ->createQueryBuilder('c')
            ->where('c.status = 1');
        $companySetting = $companySettingQuery->getQuery()->getResult();

        if(!$companySetting){
            $val = 1;
        }else{
            $val = 0;
        }

        return $this->render('PmsSettingBundle:CompanySetting:home.html.twig', array(
            'companySetting' => $companySetting,
            'val' => $val
        ));
    }

    public function addAction(Request $request)
    {
        $companySetting = new CompanySetting();

        $form = $this->createForm(new CompanySettingType(),  $companySetting);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $companySetting->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:CompanySetting')->create($companySetting);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Company Setting Successfully Add'
                );

                return $this->redirect($this->generateUrl('company'));
            }
        }

        return $this->render('PmsSettingBundle:CompanySetting:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function updateAction(Request $request, CompanySetting $companySetting)
    {
        $form = $this->createForm(new CompanySettingType(),  $companySetting);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $companySetting->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:CompanySetting')->create($companySetting);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Company Setting Successfully Update'
                );

                return $this->redirect($this->generateUrl('company'));
            }
        }

        return $this->render('PmsSettingBundle:CompanySetting:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
} 