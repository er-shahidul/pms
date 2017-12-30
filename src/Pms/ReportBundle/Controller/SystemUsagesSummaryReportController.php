<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\SystemUsagesSummaryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class SystemUsagesSummaryReportController extends Controller
{
    public function systemUsagesSummaryReportSearchForm($request)
    {
        $form = new SystemUsagesSummaryType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function systemUsagesSummaryReportAction(Request $request)
    {
        list($form, $data) = $this->systemUsagesSummaryReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($totalPrItemApproved, $totalItemClaimed, $totalPoItems, $totalDeliveredItem, $totalPrRaised, $totalPrApproved, $month,$totalPoQuantity)
                = $this->getDoctrine()
                ->getRepository('PmsCoreBundle:PurchaseRequisition')
                ->systemUsagesSummaryReport($data);

        return $this->render('PmsReportBundle:Report:systemUsagesSummaryReport.html.twig', array(
                'formSearch' => $form->createView(),
                'totalPrItemApproved' =>$totalPrItemApproved,
                'totalItemClaimed' =>$totalItemClaimed,
//                'totalPoIssued' =>$totalPoIssued,
                'totalPoItems' =>$totalPoItems,
                'totalDeliveredItem' =>$totalDeliveredItem,
                'totalPrRaised' =>$totalPrRaised,
                'totalPrApproved' =>$totalPrApproved,
                'month' =>$month,
                'totalPoQuantity' =>$totalPoQuantity,
        ));
    }
}