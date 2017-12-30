<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\TermsAndConditions;
use Pms\SettingBundle\Form\TermsAndConditionsType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * TermsAndConditions controller.
 *
 */
class TermsAndConditionsController extends Controller
{
    public function indexAction(Request $request, $status = 'active')
    {
        $termsAndConditionRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions');

        $termsAndCondition = new TermsAndConditions();

        $form = $this->createForm(new TermsAndConditionsType(), $termsAndCondition);

        $termsAndConditions = $this->paginate($termsAndConditionRepository->getTermsAndConditionSearch($status));

        return $this->render('PmsSettingBundle:TermsAndConditions:home.html.twig', array(
            'termsAndConditions' => $termsAndConditions,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function refreshAction()
    {
        $termsAndConditionRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions');

        $termsAndConditions = $this->paginate($termsAndConditionRepository->getTermsAndConditionSearch('active'));

        return $this->render('PmsSettingBundle:TermsAndConditions:refresh.html.twig', array(
            'termsAndConditions' => $termsAndConditions,
        ));
    }

    public function deleteAction(TermsAndConditions $termsAndConditions)
    {
        $termsAndConditions->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->update($termsAndConditions);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'TermsAndConditions Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('terms_and_conditions'));
    }

    public function activeAction(TermsAndConditions $termsAndConditions)
    {
        $termsAndConditions->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->update($termsAndConditions);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'TermsAndConditions Successfully Restored'
        );

        return $this->redirect($this->generateUrl('terms_and_conditions'));
    }

    public function updateAction(Request $request, TermsAndConditions $termsAndConditions, $status = 'active')
    {
        $termsAndConditionRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions');

        $form = $this->createForm(new TermsAndConditionsType(), $termsAndConditions);

        $termsAndConditions = $this->paginate($termsAndConditionRepository->getTermsAndConditionSearch($status));

        return $this->render('PmsSettingBundle:TermsAndConditions:home.html.twig', array(
            'termsAndConditions' => $termsAndConditions,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function checkAction(Request $request)
    {
        $termsAndConditions = $request->request->get('termsAndConditions');

        $conditionCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->findOneBy(
            array('condition' => $termsAndConditions )
        );

        if ($conditionCheck) {
            $return = array("responseCode" => 200, "condition" => "termsAndConditions already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "condition" => "termsAndConditions available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function addAction(Request $request)
    {
        $conditionArray = $request->request->get('conditionArray');
        $conditionArray = explode(',',$conditionArray);

        $condition = $conditionArray[0];
        $updateId = $conditionArray[1];
        $sector = $conditionArray[2];

        if($condition && $sector) {
            $termsAndConditions = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->find($updateId);
            $conditionCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->findOneBy(
                array('condition' => $condition )
            );
            if($termsAndConditions) {
                $termsAndConditions->setCondition($condition);
                $termsAndConditions->setSector($this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->find($sector));
                $this->getDoctrine()->getManager()->persist($termsAndConditions);
                $this->getDoctrine()->getManager()->flush();

                $return = array("responseCode" => 202);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } elseif($conditionCheck) {

                $return = array("responseCode" => 200);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $termsAndConditions = new TermsAndConditions();
                $termsAndConditions->setCondition($condition);
                $termsAndConditions->setSector($this->getDoctrine()->getRepository('PmsSettingBundle:PurchaseType')->find($sector));
                $termsAndConditions->setCreatedBy($this->getUser());
                $termsAndConditions->setCreatedDate(new \DateTime());
                $termsAndConditions->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:TermsAndConditions')->create($termsAndConditions);

                $return = array("responseCode" => '404');
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }
        } else {
            $return = array("responseCode" => 204);
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function paginate($dql)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        if (is_string($dql)) {
            $query = $em->createQuery($dql);
        } else {
            $query = $dql;
        }

        $paginator = $this->get('knp_paginator');
        $value = $paginator->paginate(
            $query,
            $page = $this->get('request')->query->get('page', 1) /*page number*/,
            50/*limit per page*/
        );

        return $value;
    }
}
