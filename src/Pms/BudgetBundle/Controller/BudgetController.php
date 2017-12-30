<?php

namespace Pms\BudgetBundle\Controller;

use DateTime;
use Doctrine\ORM\Repository;
use Pms\BudgetBundle\Entity\AdditionalBudgetForSubCategory;
use Pms\BudgetBundle\Entity\Budget;
use Pms\BudgetBundle\Entity\BudgetComment;
use Pms\BudgetBundle\Entity\BudgetForSubCategory;
use Pms\BudgetBundle\Entity\Log\BudgetLog;
use Pms\BudgetBundle\Form\AdditionalSubcategoryListType;
use Pms\BudgetBundle\Form\BudgetEditType;
use Pms\BudgetBundle\Form\BudgetType;
use Pms\BudgetBundle\Form\ProjectListType;
use Pms\BudgetBundle\Form\SearchForm\DateSearchType;
use Pms\BudgetBundle\Form\SearchForm\ProjectSearchType;
use Pms\SettingBundle\Entity\Project;
use Pms\SettingBundle\Entity\SubCategory;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends Controller
{
    public function budgetSearchForm($request)
    {
        $formSearch = new ProjectSearchType($this->getUser());
        $formDate = new DateSearchType();

        $data = $request->query->get($formSearch->getName());
        $dataDate = $request->query->get($formDate->getName());

        return array($formSearch, $formDate, $data, $dataDate);
    }

    public function indexAction(Request $request, $status = 'all')
    {
        list($formSearch, $formDate, $data, $dataDate) = $this->budgetSearchForm($request);

        $budgetRepository = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget');

        $budgets = $this->paginate($budgetRepository->getBudgetSearch($this->getUser(), $dataDate, $data, $status));

        $form = $this->createForm($formSearch, $data);
        $form->submit($data);
        $formDate = $this->createForm($formDate, $dataDate);
        $formDate->submit($dataDate);

        return $this->render('PmsBudgetBundle:Budget:home.html.twig', array(
            'budgets' => $budgets,
            'status' => $status,
            'form' => $form->createView(),
            'formDate' => $formDate->createView(),
            'url'=>$request->server->get('REQUEST_URI')
        ));
    }

    public function detailsAction(Budget $budget)
    {
        return $this->render('PmsBudgetBundle:Budget:details.html.twig', array(
            'budget' => $budget,
        ));
    }
    public function detailsForMobAction(Budget $budget)
    {
        return $this->render('PmsBudgetBundle:Budget:detailsForMob.html.twig', array(
            'budget' => $budget,
        ));
    }

    public function editAction(Request $request, Budget $budget)
    {
        $subCategoryQuery = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')
            ->createQueryBuilder('sc')
            ->select('sc.subCategoryName')
            ->addSelect('sc.id')
            ->where('sc.status = 1');
        $subCategories     = $subCategoryQuery->getQuery()->getResult();

        foreach ($subCategories as $subCategory) {
            $subCategoryNameArr[]    = $subCategory['subCategoryName'];
            $subCategoryIdArr[]      = $subCategory['id'];
        }

        $form = $this->createForm(new BudgetEditType(), $budget);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $budget->setUpdatedBy($this->getUser());
                $budget->setUpdatedDate(new \DateTime());

                $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Budget Successfully Update'
                );

                return $this->redirect($this->generateUrl('budget'));
            }
        }

        return $this->render('PmsBudgetBundle:Budget:formEdit.html.twig', array(
            'form' => $form->createView(),
            'budget' => $budget,
            'subCategoryNameArr' => $subCategoryNameArr,
            'subCategoryIdArr' => $subCategoryIdArr,
        ));
    }

    public function findBudgetAction(Request $request)
    {
        $budgetArray = $request->request->get('budgetArray');
        $budgetArray = explode(',',$budgetArray);

        $month = $budgetArray[0];
        $project = $budgetArray[1];
        $subCategory = $budgetArray[2];

        $monthStart = $month;
        $monthEnd = date('Y-m-t',strtotime ($monthStart));

        $budgetMonth = $this->getDoctrine()->getRepository('PmsBudgetBundle:BudgetForSubCategory')->getBudgetTotalForPrFormMSG($month, $project, $subCategory);
        $purchaseRequisition = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getPrTotalForPrFormMSG($monthStart, $monthEnd, $project, $subCategory);

        if ($budgetMonth) {
            $response = new Response(json_encode(array(
                'total' => $purchaseRequisition[0]["total"],
                'netTotal' => $budgetMonth[0]["amount"]
            )));
            $response->headers->set('Content-Type', 'application/json');
        } else {
            $response = new Response(json_encode(array(
                'total' => $purchaseRequisition[0]["total"],
                'netTotal' => 'No Budget Added'
            )));
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }

    public function printAction(Budget $budget)
    {
        return $this->render('PmsBudgetBundle:Budget:print.html.twig', array(
            'budget' => $budget,
        ));
    }

    public function refreshAction(Budget $budget)
    {
        return $this->render('PmsBudgetBundle:Budget:refresh.html.twig', array(
            'budget' => $budget,
        ));
    }

    public function pdfAction(Budget $budget)
    {
        $html = $this->renderView(
            'PmsBudgetBundle:Budget:pdf.html.twig', array(
                'budget' => $budget,
            )
        );

        $dompdf = $this->get('slik_dompdf');
        // Generate the pdf
        $dompdf->getpdf($html);
        $dompdf->stream("budget.pdf");
        $pdfoutput = $dompdf->output();
    }

    public function holdAction(Budget $budget)
    {
        $budget->setStatus(5);

        $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);

        $this->keepLog($budget, 5);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Budget Successfully Hold'
        );

        return $this->redirect($this->generateUrl('budget'));
    }

    /**
     * @param Budget $budget
     * @param $status
     */
    protected function keepLog(Budget $budget, $status)
    {
        $poLog = new BudgetLog();

        $poLog->setBudget($budget);
        $poLog->setCreatedBy($this->getUser());
        $poLog->setStatus($status);
        $this->getDoctrine()->getRepository('PmsBudgetBundle:Log\BudgetLog')->create($poLog);
    }

    public function openAction(Budget $budget)
    {
        $budget->setStatus(1);

        $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);

        $this->keepLog($budget, 1);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Budget Successfully Open'
        );

        return $this->redirect($this->generateUrl('budget'));
    }

    public function cancelAction(Budget $budget)
    {
        $budget->setStatus(6);

        $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);

        $this->keepLog($budget, 6);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Budget Successfully Cancel'
        );

        return $this->redirect($this->generateUrl('budget'));
    }

    public function approveOneAction(Budget $budget)
    {
        $budget->setApproveStatus(1);
        $budget->setApprovedOne($this->getUser());
        $budget->setApprovedOneDate(new \DateTime());

        $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Budget Successfully Approved'
        );

        return $this->redirect($this->generateUrl('budget'));
    }

    public function approveTwoAction(Budget $budget)
    {
        $budget->setApproveStatus(2);
        $budget->setApprovedTwo($this->getUser());
        $budget->setApprovedTwoDate(new \DateTime());

        $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Budget Successfully Approved'
        );

        return $this->redirect($this->generateUrl('budget'));
    }

    public function approveThreeAction(Budget $budget)
    {
        $budget->setApproveStatus(3);
        $budget->setApprovedThree($this->getUser());
        $budget->setApprovedThreeDate(new \DateTime());

        $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Budget Successfully Approved'
        );

        return $this->redirect($this->generateUrl('budget'));
    }

    public function approveOneAjaxAction(Request $request)
    {
        $id = $request->request->get('budgetId');

        if($id){
            $budget = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->find($id);

            $budget->setApproveStatus(1);
            $budget->setApprovedOne($this->getUser());
            $budget->setApprovedOneDate(new \DateTime());

            $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);

            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }

        return new Response($return);
    }

    public function approveTwoAjaxAction(Request $request)
    {
        $id = $request->request->get('budgetId');

        if($id){
            $budget = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->find($id);

            $budget->setApproveStatus(2);
            $budget->setApprovedTwo($this->getUser());
            $budget->setApprovedTwoDate(new \DateTime());

            $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);

            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }

        return new Response($return);
    }

    public function attachmentViewAction(Request $request, Budget $budget, $index)
    {
        if(null !== $response = $this->checkAttachFileByIndex($budget, $index)) {
            return $response;
        }

        if($index == 1){
            $path = $budget->getPath();
        }
//        elseif($index == 2){
//            $path = $budget->getPathTwo();
//        }elseif($index == 3){
//            $path = $budget->getPathThree();
//        }

        return $this->render('PmsBudgetBundle:Budget:viewer.html.twig', array(
            'budget' => $budget,
            'path' => $path,
        ));
    }

    private function checkAttachFileByIndex(Budget $budget, $index)
    {
        if (null == $fileName = $budget->getAbsolutePathByIndex($index)) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    public function approveThreeAjaxAction(Request $request)
    {
        $id = $request->request->get('budgetId');

        if($id){
            $budget = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->find($id);

            $budget->setApproveStatus(3);
            $budget->setApprovedThree($this->getUser());
            $budget->setApprovedThreeDate(new \DateTime());

            $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);

            $return = json_encode(array("responseCode" => 202));
        } else {
            $return = json_encode(array("responseCode" => 204));
        }

        return new Response($return);
    }

    public function commentAction(Request $request)
    {
        $budgetCommentArray = $request->request->get('budgetCommentArray');
        $budgetCommentArray = explode(',', $budgetCommentArray);

        $comment = $budgetCommentArray[0];
        $budgetId = $budgetCommentArray[1];

        if ($comment) {
            $commentBudget = new BudgetComment();

            $commentBudget->setComment($comment);
            $commentBudget->setCreatedBy($this->getUser());
            $commentBudget->setCreatedDate(new \DateTime());
            $commentBudget->setBudget($this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->find($budgetId));

            $this->getDoctrine()->getRepository('PmsBudgetBundle:BudgetComment')->create($commentBudget);

            $return = array("responseCode" => 202);
        } else {

            $return = array("responseCode" => 204);
        }

        return new Response(json_encode($return));
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

    public function addAction(Request $request, Project $project)
    {
        $budget = new Budget();

        $subCategoryQuery = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')
            ->createQueryBuilder('sc')
            ->select('sc.id')
            ->where('sc.status = 1');
        $subCategories     = $subCategoryQuery->getQuery()->getResult();

        $totalCategories = array();
        $j = 0;
        foreach ($subCategories as $subCategory) {
            $j = $j + 1;
            $totalCategories[] = $j;

            $subCategoryName         = $this->getDoctrine()->getRepository('PmsSettingBundle:SubCategory')->find($subCategory);
            $subCategoryNameArr[]    = $subCategoryName->getSubCategoryName();
            $subCategoryIdArr[]      = $subCategoryName->getId();

            $budgetForSubCategory = new BudgetForSubCategory();
            $budget->addBudgetForSubCategory($budgetForSubCategory);
        }

        $form = $this->createForm(new BudgetType($project->getId(), $this->getUser()), $budget);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                if ($form->get('submit')->isClicked()) {
                    $month = $form->get('month')->getData();
                    $month = $month->format('Y-m-d');

                    $budgetCheckQuery = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')
                        ->createQueryBuilder('b')
                        ->where('b.project = :project')
                        ->andWhere('b.month = :month')
                        ->setParameter('project', $project->getId())
                        ->setParameter('month', $month);
                    $budgetCheck = $budgetCheckQuery->getQuery()->getArrayResult();

                    if ($budgetCheck) {
                        $this->get('session')->getFlashBag()->add(
                            'notice',
                            'This budget already exist.'
                        );
                        goto end;
                    }
                }

                $budget->setCreatedBy($this->getUser());
                $budget->setCreatedDate(new \DateTime());
                $budget->setStatus(1);
                $budget->setApproveStatus(0);
                $budget->setProject($project);

                /** @var BudgetForSubCategory $project */
                foreach ($budget->getBudgetForSubCategories() as $subCategory) {

                    $subCategory->setBudget($budget);
                }

                $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->create($budget);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Budget Add Successfully'
                );

                return $this->redirect($this->generateUrl('budget'));
            }
        }

        end:
        return $this->render('PmsBudgetBundle:Budget:form.html.twig', array(
            'form' => $form->createView(),
            'projectName' => $project->getProjectName(),
            'subCategoryNameArr' => $subCategoryNameArr,
            'subCategoryIdArr' => $subCategoryIdArr,
        ));
    }

    public function projectListAction()
    {
        $data['userId'] = $this->getUser()->getId();
        $data['role']   = $this->getUser()->getRole();
        $budget         = new Budget();
        $form           = $this->createForm(new ProjectListType($data), $budget);

        return $this->render('PmsBudgetBundle:Budget:projectList.html.twig',array(
                'form' => $form->createView(),
            )
        );
    }
    public function additionalSubcategoryListForBudgetAction(Request $request,Budget $budget)
    {

      $url = $request->server->get('HTTP_REFERER');
        $data['userId'] = $this->getUser()->getId();
        $data['role']   = $this->getUser()->getRole();
        $additionalBudget   = new AdditionalBudgetForSubCategory();
        $form  = $this->createForm(new AdditionalSubcategoryListType($data), $additionalBudget);

        $budgetNetTotal = $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->find($budget->getId());


        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $budgetForSubCategoryNetTotal = $this->getDoctrine()
                                                     ->getRepository('PmsBudgetBundle:BudgetForSubCategory')
                                                     ->findBy(array('subCategory'=>$additionalBudget->getSubCategory()->getId(),'budget'=>$budget->getId()));

                $additionalBudget->setCreatedDate(new \DateTime());
                $additionalBudget->setBudget($budget);

                $budgetTotal = $budgetNetTotal->getNetTotal()+$additionalBudget->getAmount();
                $budget->setNetTotal($budgetTotal);

                $subcategoryBudgetTotal = $budgetForSubCategoryNetTotal[0]->getAmount()+ $additionalBudget->getAmount();
                $budgetForSubCategoryNetTotal[0]->setAmount($subcategoryBudgetTotal);

                $this->getDoctrine()->getRepository('PmsBudgetBundle:AdditionalBudgetForSubCategory')->create($additionalBudget);
                $this->getDoctrine()->getRepository('PmsBudgetBundle:Budget')->update($budget);
                $this->getDoctrine()->getRepository('PmsBudgetBundle:BudgetForSubCategory')->update($budgetForSubCategoryNetTotal[0]);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Additional Budget Successfully Inserted'
                );

                return $this->redirect($url,'301');
            }
        }

        return $this->render('PmsBudgetBundle:Budget:subcategoryList.html.twig',array(
                'form' => $form->createView(),
                'budget' => $budget
            )
        );
    }
}
