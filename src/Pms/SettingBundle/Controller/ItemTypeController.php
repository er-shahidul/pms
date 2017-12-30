<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\SettingBundle\Entity\ItemType;
use Pms\SettingBundle\Form\ItemTypeType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * ItemType controller.
 *
 */
class ItemTypeController extends Controller
{
    public function indexAction(Request $request, $status = 'active')
    {
        $itemTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType');

        $itemType = new ItemType();

        $form = $this->createForm(new ItemTypeType(), $itemType);

        $itemTypes = $this->paginate($itemTypeRepository->getItemTypeSearch($status));

        return $this->render('PmsSettingBundle:ItemType:home.html.twig', array(
            'itemTypes' => $itemTypes,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function refreshAction()
    {
        $itemTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType');

        $itemTypes = $this->paginate($itemTypeRepository->getItemTypeSearch('active'));

        return $this->render('PmsSettingBundle:ItemType:refresh.html.twig', array(
            'itemTypes' => $itemTypes,
        ));
    }

    public function deleteAction(ItemType $itemType)
    {
        $itemType->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType')->update($itemType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'ItemType Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('item_type', array('status' => 'active')));
    }

    public function activeAction(ItemType $itemType)
    {
        $itemType->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType')->update($itemType);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'ItemType Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('item_type', array('status' => 'active')));
    }

    public function updateAction(Request $request, ItemType $itemType, $status = 'active')
    {
        $itemTypeRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType');

        $form = $this->createForm(new ItemTypeType(), $itemType);

        $itemTypes = $this->paginate($itemTypeRepository->getItemTypeSearch($status));

        return $this->render('PmsSettingBundle:ItemType:home.html.twig', array(
            'itemTypes' => $itemTypes,
            'status' => $status,
            'form' => $form->createView(),
        ));
    }

    public function checkAction(Request $request)
    {
        $itemType = $request->request->get('itemType');

        $itemTypeCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType')->findOneBy(
            array('itemType' => $itemType )
        );

        if ($itemTypeCheck) {
            $return = array("responseCode" => 200, "item_type_name" => "Item Type name already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "item_type_name" => "Item Type name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function addAction(Request $request)
    {
        $itemTypeArray = $request->request->get('itemTypeArray');
        $itemTypeArray = explode(',',$itemTypeArray);

        $itemType = $itemTypeArray[0];
        $updateId = $itemTypeArray[1];

        if($itemType) {
            $itemTypes = $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType')->find($updateId);
            $itemTypeCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType')->findOneBy(
                array('itemType' => $itemType )
            );
            if($itemTypes) {
                $itemTypes->setItemType($itemType);
                $this->getDoctrine()->getManager()->persist($itemTypes);
                $this->getDoctrine()->getManager()->flush();

                $return = array("responseCode" => 202);
                $return = json_encode($return);

                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } elseif($itemTypeCheck) {

                $return = array("responseCode" => 200);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            } else {
                $itemTypes = new ItemType();
                $itemTypes->setItemType($itemType);
                $itemTypes->setCreatedBy($this->getUser());
                $itemTypes->setCreatedDate(new \DateTime());
                $itemTypes->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:ItemType')->create($itemTypes);

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
