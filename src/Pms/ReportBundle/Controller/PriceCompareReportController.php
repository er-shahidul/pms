<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\ReportBundle\Form\PriceCompareReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class PriceCompareReportController extends Controller
{
    public function PriceCompareSearchForm($request){

        $form = new PriceCompareReportSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function PriceCompareReportAction(Request $request)
    {
        list($form, $data) = $this->PriceCompareSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        list($priceCompare, $monthArray) = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrderItem')->getPriceCompareReport($data);

        return $this->render('PmsReportBundle:Report:priceCompareReport.html.twig', array(
            'priceCompare' => $priceCompare,
            'monthArray' => $monthArray,
            'form' => $form->createView(),
        ));
    }
} 