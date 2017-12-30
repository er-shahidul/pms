<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\Category;
use Pms\SettingBundle\Form\CategoryType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Category controller.
 *
 */
class CategoryController extends Controller
{
    public function indexAction(Request $request, $status = 'active')
    {
        $categoryRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Category');

        $category = new Category();

        $form = $this->createForm(new CategoryType(), $category);

        $categories = $this->paginate($categoryRepository->getCategorySearch($status));

        return $this->render('PmsSettingBundle:Category:home.html.twig', array(
            'categories' => $categories,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function refreshAction()
    {
        $categoryRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Category');

        $categories = $this->paginate($categoryRepository->getCategorySearch('active'));

        return $this->render('PmsSettingBundle:Category:refresh.html.twig', array(
            'categories' => $categories,
        ));
    }

    public function deleteAction(Category $category)
    {
        $category->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Category')->update($category);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Category Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('category'));
    }

    public function activeAction(Category $category)
    {
        $category->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Category')->update($category);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Category Successfully Restored'
        );

        return $this->redirect($this->generateUrl('category'));
    }

    public function updateAction(Request $request, Category $category, $status = 'active')
    {
        $categoryRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Category');

        $form = $this->createForm(new CategoryType(), $category);

        $categories = $this->paginate($categoryRepository->getCategorySearch($status));

        return $this->render('PmsSettingBundle:Category:home.html.twig', array(
            'categories' => $categories,
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function checkAction(Request $request)
    {
        $categoryName = $request->request->get('categoryName');

        $category = $this->getDoctrine()->getRepository('PmsSettingBundle:Category')->findOneBy(
            array('categoryName' => $categoryName )
        );

        if ($category) {
            $return = array("responseCode" => 200, "category_name" => "Category already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "category_name" => "Category name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function addAction(Request $request)
    {
        $categoryArray = $request->request->get('categoryArray');
        $categoryArray = explode(',',$categoryArray);

        $categoryName = $categoryArray[0];
        $updateId = $categoryArray[1];

        if($categoryName) {
            $category = $this->getDoctrine()->getRepository('PmsSettingBundle:Category')->find($updateId);
            $categoryNameCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:Category')->findOneBy(
                array('categoryName' => $categoryName )
            );
            if($category) {
                $category->setCategoryName($categoryName);
                $this->getDoctrine()->getManager()->persist($category);
                $this->getDoctrine()->getManager()->flush();

                $return = array("responseCode" => 202);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } elseif($categoryNameCheck) {
                $return = array("responseCode" => 200);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $category = new Category();
                $category->setCategoryName($categoryName);
                $category->setCreatedBy($this->getUser());
                $category->setCreatedDate(new \DateTime());
                $category->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:Category')->create($category);

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