<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\CoreBundle\Entity\Payment;
use Pms\ReportBundle\Form\VendorPaymentReportSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Knp\Snappy\Pdf;

/**
 * Report controller.
 *
 */
class SupplierPaymentReportController extends Controller
{
    public function supplierPaymentReportSearchForm($request)
    {
        $form = new VendorPaymentReportSearchType();
        $data = $request->get($form->getName());

        return array($form, $data);
    }

    public function supplierPaymentReportAction(Request $request)
    {
        list($form, $data) = $this->supplierPaymentReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
        $data['vendor'] = $request->query->get('vendor');

      //  $payments = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->supplierPaymentReport($data);

        $payments = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->supplierPaymentReport($data);


        return $this->render('PmsReportBundle:Report:supplierPaymentReport.html.twig', array(
            'payments' => $payments,
            'form' => $form->createView(),
        ));
    }

    public function supplierPaymentReportAutoVendorNameAction(Request $request)
    {
        $vendorQuery = array();
        $vendorName = $request->query->get('q', null);
        $vendor = $request->query->get('vendor',  null);
        if ($vendorName) {
            $vendorQuery = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')
                ->vendorAutoComplete($vendorName, $this->getUser());

        } else if ($vendor) {
            $vendor = $this->getDoctrine()->getRepository('PmsSettingBundle:Vendor')->find($vendor);
            $vendorQuery = array('id' => $vendor->getId(), 'text' => $vendor->getVendorName());
        }

        return new JsonResponse($vendorQuery);
    }

    public function supplierPaymentReportPdfAction(Request $request)
    {
        list($form, $data) = $this->supplierPaymentReportSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);
        $data['vendor'] = $request->request->get('vendor');

       // $payments = $this->getDoctrine()->getRepository('PmsCoreBundle:Payment')->supplierPaymentReport($data);
        $payments = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')->supplierPaymentReport($data);

        $html = $this->renderView(
            'PmsReportBundle:Report:supplierPaymentReportPdf.html.twig', array(
                'payments' => $payments,
                'form' => $form->createView(),
            )
        );

        $wkhtmltopdfPath = 'xvfb-run --server-args="-screen 0, 1280x1024x24" /usr/bin/wkhtmltopdf --use-xserver';
        $snappy = new Pdf($wkhtmltopdfPath);
        $pdf = $snappy->getOutputFromHtml($html);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="supplierReport.pdf"');
        echo $pdf;

        return new Response('');
    }
}