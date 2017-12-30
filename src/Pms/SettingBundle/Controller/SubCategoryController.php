<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\Category;
use Pms\SettingBundle\Entity\SubCategory;
use Pms\SettingBundle\Form\SubCategoryType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SubCategory controller.
 *
 */
class SubCategoryController extends Controller
{
    public function indexAction(Request $request, $status = 'active')
    {
        $subCategoryRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory');

        $subCategory = new SubCategory();

        $form = $this->createForm(new SubCategoryType(), $subCategory);

        $subCategories = $this->paginate($subCategoryRepository->getSubCategorySearch($status));

        return $this->render('PmsSettingBundle:SubCategory:home.html.twig', array(
            'subCategories' => $subCategories,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function refreshAction()
    {
        $subCategoryRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory');

        $subCategories = $this->paginate($subCategoryRepository->getSubCategorySearch('active'));

        return $this->render('PmsSettingBundle:SubCategory:refresh.html.twig', array(
            'subCategories' => $subCategories,
        ));
    }

    public function deleteAction(SubCategory $subCategory)
    {
        $subCategory->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->update($subCategory);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Sub Category Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('sub_category'));
    }

    public function activeAction(SubCategory $subCategory)
    {
        $subCategory->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->update($subCategory);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Sub Category Successfully Restored'
        );

        return $this->redirect($this->generateUrl('sub_category'));
    }

    public function updateAction(Request $request, SubCategory $subCategory, $status = 'active')
    {
        $subCategoryRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory');

        $form = $this->createForm(new SubCategoryType(), $subCategory);

        $subCategories = $this->paginate($subCategoryRepository->getSubCategorySearch($status));

        return $this->render('PmsSettingBundle:SubCategory:home.html.twig', array(
            'subCategories' => $subCategories,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function checkAction(Request $request)
    {
        $subCategoryName = $request->request->get('subCategoryName');

        $subCategory = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->findOneBy(
            array('subCategoryName' => $subCategoryName )
        );

        if ($subCategory) {
            $return = array("responseCode" => 200, "sub_category_name" => "Sub Category already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "sub_category_name" => "Sub Category name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function addAction(Request $request)
    {
        $subCategoryArray = $request->request->get('subCategoryArray');
        $subCategoryArray = explode(',',$subCategoryArray);

        $subCategoryName = $subCategoryArray[0];
        $category = $subCategoryArray[1];
        $updateId = $subCategoryArray[2];
        $categoryHead = $subCategoryArray[3];
        $categorySubHead = $subCategoryArray[4];

        if($subCategoryName && $category && $categoryHead && $categorySubHead) {
            $subCategory = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->find($updateId);
            $subCategoryNameCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->findOneBy(
                array('subCategoryName' => $subCategoryName )
            );
            if($subCategory) {
                $subCategory->setSubCategoryName($subCategoryName);
                $subCategory->setHead($this->getDoctrine()->getRepository('UserBundle:User')->find($categoryHead));
                $subCategory->setSubHead($this->getDoctrine()->getRepository('UserBundle:User')->find($categorySubHead));
                $subCategory->setCategory($this->getDoctrine()->getRepository('PmsSettingBundle:Category')->find($category));
                $this->getDoctrine()->getManager()->persist($subCategory);
                $this->getDoctrine()->getManager()->flush();

                $return = array("responseCode" => 202);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } elseif($subCategoryNameCheck) {
                $return = array("responseCode" => 200);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $subCategory = new SubCategory();
                $subCategory->setSubCategoryName($subCategoryName);
                $subCategory->setHead($this->getDoctrine()->getRepository('UserBundle:User')->find($categoryHead));
                $subCategory->setSubHead($this->getDoctrine()->getRepository('UserBundle:User')->find($categorySubHead));
                $subCategory->setCategory($this->getDoctrine()->getRepository('PmsSettingBundle:Category')->find($category));
                $subCategory->setCreatedBy($this->getUser());
                $subCategory->setCreatedDate(new \DateTime());
                $subCategory->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->create($subCategory);

                $return = array("responseCode" => 404);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }
        } else{
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