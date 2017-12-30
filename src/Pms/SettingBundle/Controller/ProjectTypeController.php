<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\ProjectType;
use Pms\SettingBundle\Form\ProjectTypeType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Company Type controller.
 *
 */
class ProjectTypeController extends Controller
{
    public function indexAction(Request $request, $status = 'active')
    {
        $projectTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType');

        $projectType = new ProjectType();

        $form = $this->createForm(new ProjectTypeType(), $projectType);

        $projectTypes = $this->paginate($projectTypeRepository->getProjectTypeSearch($status));

        return $this->render('PmsSettingBundle:ProjectType:home.html.twig', array(
            'projectTypes' => $projectTypes,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function refreshAction()
    {
        $projectTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType');

        $projectTypes = $this->paginate($projectTypeRepository->getProjectTypeSearch('active'));

        return $this->render('PmsSettingBundle:ProjectType:refresh.html.twig', array(
            'projectTypes' => $projectTypes,
        ));
    }

    public function deleteAction(ProjectType $projectType)
    {
        $projectType->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType')->update($projectType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Company Type Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('project_type'));
    }

    public function activeAction(ProjectType $projectType)
    {
        $projectType->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType')->update($projectType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Company Type Successfully Restored'
        );

        return $this->redirect($this->generateUrl('project_type'));
    }

    public function updateAction(Request $request, ProjectType $projectType, $status = 'active')
    {
        $projectTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType');

        $form = $this->createForm(new ProjectTypeType(), $projectType);

        $projectTypes = $this->paginate($projectTypeRepository->getProjectTypeSearch($status));

        return $this->render('PmsSettingBundle:ProjectType:home.html.twig', array(
            'projectTypes' => $projectTypes,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function checkAction(Request $request)
    {
        $projectCategoryName = $request->request->get('projectCategoryName');

        $projectTypeCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType')->findOneBy(
            array('projectCategoryName' => $projectCategoryName )
        );

        if ($projectTypeCheck) {
            $return = array("responseCode" => 200, "project_type" => "Project Category name already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "project_type" => "Project Category name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function addAction(Request $request)
    {
        $projectCategoryArray = $request->request->get('projectCategoryArray');
        $projectCategoryArray = explode(',',$projectCategoryArray);

        $projectCategoryName = $projectCategoryArray[0];
        $updateId = $projectCategoryArray[1];

        if($projectCategoryName) {
            $projectType = $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType')->find($updateId);
            $projectTypeNameCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType')->findOneBy(
                array('projectCategoryName' => $projectCategoryName )
            );
            if($projectType) {
                $projectType->setProjectCategoryName($projectCategoryName);
                $this->getDoctrine()->getManager()->persist($projectType);
                $this->getDoctrine()->getManager()->flush();

                $return = array("responseCode" => 202);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } elseif($projectTypeNameCheck) {

                $return = array("responseCode" => 200);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $projectType = new ProjectType();
                $projectType->setProjectCategoryName($projectCategoryName);
                $projectType->setCreatedBy($this->getUser());
                $projectType->setCreatedDate(new \DateTime());
                $projectType->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:ProjectType')->create($projectType);

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