<?php

namespace Pms\InventoryBundle\Controller;

use Doctrine\ORM\EntityManager;
use Pms\CoreBundle\Controller\BaseController;
use Pms\SettingBundle\Entity\Project;
use Pms\InventoryBundle\Entity\Delivery;
use Pms\InventoryBundle\Entity\TotalReceiveItem;
use Pms\InventoryBundle\Entity\DailyInventory;
use Pms\InventoryBundle\Form\DeliveryType;
use Pms\InventoryBundle\Form\ProjectListType;
use Pms\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InventoryController extends BaseController
{
    public function indexAction(Request $request)
    {

         $data = $request->query->all();

        $deliveryLists = $this->getDoctrine()
                              ->getRepository('PmsInventoryBundle:DeliveryItem')
                              ->getDeliveryData($data, $this->getUser(),'individual');

        $projects = $this->getDoctrine()
                         ->getRepository('PmsSettingBundle:Project')
                         ->getProjectForUser($this->getUser());


        $deliveryList  = $this->paginate($deliveryLists);

        return $this->render(
            'PmsInventoryBundle:Delivery:list.html.twig',
            array(
                'deliveryLists' => $deliveryList,
                'projects'    => $projects
            )
        );
    }
    public function projectToProjectDeliveryListAction(Request $request)
    {
         $data = $request->query->all();

        $deliveryLists = $this->getDoctrine()
                              ->getRepository('PmsInventoryBundle:DeliveryItem')
                              ->getDeliveryData($data, $this->getUser(),'project');

        $projects = $this->getDoctrine()
                         ->getRepository('PmsSettingBundle:Project')
                         ->getProjectForUser($this->getUser());


        $deliveryList  = $this->paginate($deliveryLists);

        return $this->render(
            'PmsInventoryBundle:Delivery:listProjectToProject.html.twig',
            array(
                'deliveryLists' => $deliveryList,
                'projects'    => $projects
            )
        );
    }

    public function addAction(Request $request, Project $project)
    {
        $delivery = new Delivery();

        $form = $this->createForm(new DeliveryType($project->getId(), $this->getUser(),$status = null), $delivery);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $delivery->setDeliveryType('individual');
                $this->saveDeliveryToDatabase($project, $delivery);
                $this->successMessage('Delivery Successfully Inserted');

                return $this->redirect($this->generateUrl('delivery_list'));
            }
        }

        return $this->render(
            'PmsInventoryBundle:Delivery:add.html.twig',
            array(
                'form'     => $form->createView(),
                'projects' => $project,
            )
        );
    }

    public function addProjectToProjectDeliveryAction(Request $request, Project $project)
    {
        $delivery = new Delivery();

        $form = $this->createForm(new DeliveryType($project->getId(), $this->getUser(),$status= 'project-to-project'), $delivery);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $delivery->setDeliveryType('project');

                $this->saveDeliveryToDatabase($project, $delivery);

                $this->successMessage('Delivery Successfully Inserted');

                return $this->redirect($this->generateUrl('delivery_project_to_project_list'));
            }
        }

        return $this->render(
            'PmsInventoryBundle:Delivery:addProjectToProjectDelivery.html.twig',
            array(
                'form'     => $form->createView(),
                'projects' => $project,
            )
        );
    }

    public function attachmentViewAction(Request $request, Delivery $delivery, $index)
    {

        if(null !== $response = $this->checkAttachFileByIndex($delivery, $index)) {
            return $response;
        }

        if($index == 1) {
            $path = $delivery->getPath();

        }elseif($index == 2){
            $path = $delivery->getPathTwo();
        }

        return $this->render('PmsInventoryBundle:Delivery:viewer.html.twig', array(
            'deliveryView' => $delivery,
            'path' => $path,
        ));
    }

    public function categoryWiseItemInventoryAction(Request $request)
    {
        $projectId = $request->request->get('project_id');
        $categoryId = $request->request->get('category');

        $items = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')
                                     ->getCategoryWiseItem($categoryId[0]['id'],$projectId);

        $categoryWiseItemArr = array();
        foreach ($items as $item) {
            $categoryWiseItemArr[] = $item;
        }

        $response = new Response(json_encode(array(
            'categoryWiseItem' => $categoryWiseItemArr
        )));

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    private function checkAttachFileByIndex(Delivery $delivery, $index)
    {
        if (null == $fileName = $delivery->getAbsolutePathByIndex($index)) {
            return null;
        }
        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    public function projectListAction($status)
    {

        $delivery       = new Delivery();
        $form           = $this->createForm(new ProjectListType($this->getUser()), $delivery);

        return $this->render('PmsInventoryBundle:Delivery:addList.html.twig',array(
                'form' => $form->createView(),
                'status' => $status
            )
        );
    }

    public function getItemQuantityAction(Request $request)
    {
        $item       = $request->request->get('item');
         $project_id = $request->request->get('project_id');

        $remainingQuantity = " ";

        if ($item) {
            $remainingQuantity = $this->getDoctrine()
                ->getRepository('PmsInventoryBundle:TotalReceiveItem')
                ->getItemRemainingQuantityByProjectId($item, $project_id);
//var_dump($remainingQuantity);die;
           /*$receivingQty = $this->getDoctrine()
                ->getRepository('PmsCoreBundle:Receive')
                ->getQuantityByItemAndProject($item, $project_id);

           $issuingQty = $this->getDoctrine()
               ->getRepository('PmsInventoryBundle:DailyInventory')
               ->getIssuingQty($item, $project_id);
            var_dump($openQty);
            var_dump($receivingQty);
             var_dump($issuingQty);die;*/
        }

        return new JsonResponse($remainingQuantity);
    }

    public function deleteDeliveryAction(Delivery $delivery)
    {
        $delivery->setStatus('deleted');
        $this->getDoctrine()->getRepository('PmsInventoryBundle:Delivery')->delete($delivery);
        $massage = 'Delivery Successfully Updated';
        $this->successMessage($massage);

        return $this->redirect($this->generateUrl('delivery_list'));
    }

    public function updateDeliveryAction(Request $request, Delivery $delivery)
    {
        $data['userId'] = $this->getUser()->getId();
        $data['role']   = $this->getUser()->getRole();
        $project        = $delivery->getProject();
        $form           = $this->createForm(new DeliveryType($project->getId(), $this->getUser(),$status = null), $delivery);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $deliveries = $this->saveDelivery($delivery, $project, $data);
                $this->saveTotalReceivedItemQuantity($deliveries);
                $massage = 'Delivery Successfully Updated';
                $this->successMessage($massage);

                return $this->redirect($this->generateUrl('delivery_list'));
            }
        }

        return $this->render('PmsInventoryBundle:Delivery:add.html.twig',array(
                'form'     => $form->createView(),
                'projects' => $project
            )
        );
    }

    /**
     * @param $delivery
     * @param $project
     * @param $data
     * @return Delivery
     */
    public function saveDelivery($delivery, $project, $data)
    {
        $delivery->setStatus('active');
        $delivery->setProject($project);
        $User = $this->getDoctrine()->getRepository('UserBundle:User')->findOneById($data['userId']);
        $delivery->setCreatedBy($User);
        $delivery->upload();
        $delivery->uploadTwo();
        $deliveryRepo = $this->getDoctrine()->getRepository("PmsInventoryBundle:Delivery");
        $deliveries   = $deliveryRepo->create($delivery);

        return $deliveries;
    }


    /**
     * @param $deliveries
     */
    public function saveTotalReceivedItemQuantity($deliveries)
    {
        $totalReceiveItemRepo = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem');
        $totalReceiveItemRepo->saveUsedDeliveryItem($deliveries);
    }

    /**
     * @param $deliveries
     */
    public function saveTotalReceivedItemProjectQuantity($deliveries)
    {
        $totalReceiveItemRepo = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem');
        $totalReceiveItemRepo->saveUsedDeliveryItemProject($deliveries);
    }

    public function gatePassNoCheckAction(Request $request){

        $gatePassNo = $request->request->all();

        $gatePass = $this->getDoctrine()->getRepository('PmsInventoryBundle:Delivery')->findOneBy(
            array('getPass' => $gatePassNo )
        );

        if ($gatePass) {
            $return = array("responseCode" => 200, "gate_pass" => "gate Pass No already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "gate_pass" => "gate Pass No available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }


    /**
     * @param Project $project
     * @param $delivery
     */
    public function saveDeliveryToDatabase(Project $project, Delivery $delivery)
    {

        $delivery->setStatus('active');
        $delivery->setProject($project);
        $delivery->setCreatedDate(new \DateTime(date('Y-m-d')));
        $delivery->setCreatedBy($this->getUser());
        $delivery->upload();
        $delivery->uploadTwo();
        $deliveryRepo = $this->getDoctrine()->getRepository("PmsInventoryBundle:Delivery");
        $deliveryRepo->create($delivery);

        $this->saveTotalReceivedItemQuantity($delivery);

        $this->openingQuantitySaveToDailyInventory($delivery);
    }
    /**
         * @param Project $project
         * @param $delivery
         */
        public function saveDeliveryItemProjectToDatabase(Project $project, Delivery $delivery)
        {

            $delivery->setStatus('active');
            $delivery->setProject($project);
            $delivery->setCreatedDate(new \DateTime(date('Y-m-d')));
            $delivery->setCreatedBy($this->getUser());
            $delivery->upload();
            $delivery->uploadTwo();
            $deliveryRepo = $this->getDoctrine()->getRepository("PmsInventoryBundle:Delivery");
            $deliveryRepo->create($delivery);

            $this->saveTotalReceivedItemProjectQuantity($delivery);

            $this->openingQuantitySaveToDailyInventory($delivery);
        }

    public function openingQuantitySaveToDailyInventory($deliveries)
    {
        $projectId          = $deliveries->getProject()->getId();

        $stockItemRepo      = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem');
        $dailyInventoryRepo = $this->getDoctrine()->getRepository('PmsInventoryBundle:DailyInventory');
        $receiveItemRepo    = $this->getDoctrine()->getRepository('PmsCoreBundle:ReceivedItem');
        $issuedItemRepo     = $this->getDoctrine()->getRepository('PmsInventoryBundle:Delivery');

        foreach($deliveries->getDeliveryItem() as $item) {

            $itemId                     = $item->getItem()->getId();

            $existingTotalItem          = $stockItemRepo->existingTotalItemCheck($projectId, $itemId);
            $totalReceiveItem           = $receiveItemRepo->totalDailyReceiveItem($projectId, $itemId);
            $issuedItem                 = $issuedItemRepo->totalDailyIssueItem($projectId, $itemId);
            $existingDailyInventoryItem = $dailyInventoryRepo->existingDailyItemCheck($projectId, $itemId);

            $closingQuantity =  $existingTotalItem - $issuedItem;

            if($existingDailyInventoryItem){
                $update = $dailyInventoryRepo->find($existingDailyInventoryItem[0]['id']);
                $update->setOpeningQuantity($existingTotalItem);
                $update->setReceivingQuantity($totalReceiveItem);
                $update->setIssuingQuantity($issuedItem);
                $update->setClosingQuantity($closingQuantity);
                $this->getDoctrine()->getManager()->persist($update);
                // update
            } else {
                $dailyInventory = new DailyInventory();
                $dailyInventory->setProject($deliveries->getProject());
                $dailyInventory->setItem($item->getItem());
                $dailyInventory->setCreatedDate(new \DateTime(date('Y-m-d')));
                $dailyInventory->setOpeningQuantity($existingTotalItem);
                $dailyInventory->setReceivingQuantity($totalReceiveItem);
                $dailyInventory->setIssuingQuantity($issuedItem);
                $dailyInventory->setClosingQuantity($closingQuantity);
                $dailyInventoryRepo->create($dailyInventory);
                $this->getDoctrine()->getManager()->persist($dailyInventory);
                //insert
            }
            $this->getDoctrine()->getManager()->flush();
        }
    }

    public function deliveryAutoItemCompleteAction(Request $request)
    {
        $itemQuery = array();
        $itemName = $request->query->get('q', null);
        $item = $request->query->get('item',  null);
        if ($itemName) {
            /*$itemQuery = $this->getDoctrine()->getRepository('PmsInventoryBundle:TotalReceiveItem')
                ->itemDeliveryAutoComplete($itemName, $this->getUser());*/
            $itemQuery = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')
                ->itemDeliveryAutoComplete($itemName);

        } else if ($item) {
            $item = $this->getDoctrine()->getRepository('PmsSettingBundle:Item')->find($item);
            $itemQuery = array('id' => $item->getId(), 'text' => $item->getItemName());
        }

        return new JsonResponse($itemQuery);
    }

    public function getUserFromProjectAction(Request $request)
    {
        $project = $request->request->get('project');

        $userLists = $this->getDoctrine()->getRepository('UserBundle:User')->getUserFromProject($project);

        $list = array();
        if($userLists){
            foreach ($userLists as $userList) {
                $list[] = $userList;
            }
        }else{
            $response = new Response(json_encode( array( 'list' => '' )));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $response = new Response(json_encode( array( 'list' => $list )));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
