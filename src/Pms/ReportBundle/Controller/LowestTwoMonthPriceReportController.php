<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\LowestTwoMonthPriceSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Report controller.
 *
 */
class LowestTwoMonthPriceReportController extends Controller
{
    public function lowestTwoMonthPriceSearchForm($request)
    {
        $form = new LowestTwoMonthPriceSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function lowestTwoMonthPriceAction(Request $request)
    {
        list($form, $data) = $this->lowestTwoMonthPriceSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $currentMonth = date($data['month']);
        $currentMonthLastDay = date('Y-m-t', strtotime($currentMonth));
        $lastMonth = strtotime($currentMonth.' -1 month');
        $lastMonth = date('Y-m-d', $lastMonth);
        $lastMonthLastDay = date("Y-m-t", strtotime($lastMonth));

        $currentMonthTwig = !empty($currentMonth) ? date('M-Y', strtotime($currentMonth)): date('M-Y');
        $lastMonthTwig = !empty($lastMonth) ? date('M-Y', strtotime($lastMonth)): date('M-Y');

        $lowestTwoMonthPriceReport = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getLowestTwoMonthPriceReport( $data['itemType'], $data['area'],$currentMonth, $currentMonthLastDay);
        $lowestTwoMonthPriceReportPre = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getLowestTwoMonthPriceReport($data['itemType'],$data['area'], $lastMonth, $lastMonthLastDay);
        if(!empty($lowestTwoMonthPriceReport)){
            foreach($lowestTwoMonthPriceReport as $id => $row) {
                if(isset($lowestTwoMonthPriceReportPre[$id])) {
                    $lowestTwoMonthPriceReport[$id]['old'] = $lowestTwoMonthPriceReportPre[$id];
                }
            }
        }

        return $this->render('PmsReportBundle:Report:lowestTwoMonthPriceReport.html.twig', array(
            'lowestTwoMonthPriceReport' => $lowestTwoMonthPriceReport,
            'currentMonthTwig' => $currentMonthTwig,
            'lastMonthTwig' => $lastMonthTwig,
            'form' => $form->createView(),
        ));
    }
}