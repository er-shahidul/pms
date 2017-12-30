<?php

namespace Pms\ReportBundle\Controller;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\Repository;

use PHPExcel;
use PHPExcel_Style_Protection;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use Pms\BudgetBundle\Form\SearchForm\DateSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 */
class UserActivitiesController extends Controller
{
    public function userActivitiesSearchForm($request)
    {
        $form = new DateSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function getUserByPrClaim($data)
    {
        $users = $this->getDoctrine()->getRepository('UserBundle:Group')
            ->createQueryBuilder('g')
            ->select('u.username')
            ->addSelect('u.id')
            ->where("g.roles LIKE '%ROLE_PURCHASE_REQUISITION_CLAIM%' or g.roles LIKE '%ROLE_PURCHASE_REQUISITION_ADD%'")
            ->andWhere('u.enabled = 1')
            ->join('g.users', 'u');

        return $users->getQuery()->getResult();
    }

    public function getPrCreateByUser($id, $data)
    {
        if(!empty($data)){
            $monthStart = $data['month'] .' 00:00:01';
            $monthEnd = date('Y-m-t 23:59:59',(strtotime ($monthStart) ));
        }

        $users = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')
            ->createQueryBuilder('pr')
            ->select('COUNT(pr.id) as totalPrCreate')
            ->where("pr.createdBy = :id")
            ->andWhere('pr.status != 6 or pr.status != 5');
            if(!empty($data)) {
                $users->andWhere('pr.createdDate >= :start');
                $users->andWhere('pr.createdDate <= :end');
                $users->setParameter('start', $monthStart);
                $users->setParameter('end', $monthEnd);
            }
        $users->setParameter('id', $id);

        return $users->getQuery()->getSingleResult();
    }

    public function getPriClaimByUser($id, $data)
    {
        if(!empty($data)){
            $monthStart = $data['month'] .' 00:00:01';
            $monthEnd = date('Y-m-t 23:59:59',(strtotime ($monthStart) ));
        }

        $users = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisitionItem')
            ->createQueryBuilder('pri')
            ->join('pri.purchaseRequisition', 'pr')
            ->join('pri.claimedBy', 'u')
            ->select('COUNT(pri.id) as totalPriClaim')
            ->addSelect('u.id')
            ->where("pri.claimedBy = :id")
            ->andWhere('pr.status != 6 or pr.status != 5');
            if(!empty($data)) {
                $users->andWhere('pri.claimedDate >= :start');
                $users->andWhere('pri.claimedDate <= :end');
                $users->setParameter('start', $monthStart);
                $users->setParameter('end', $monthEnd);
            }
        $users->setParameter('id', $id);

        return $users->getQuery()->getSingleResult();
    }

    private function getPoCreateByUser($id, $data)
    {
        if(!empty($data)){
            $monthStart = $data['month'] .' 00:00:01';
            $monthEnd = date('Y-m-t 23:59:59',(strtotime ($monthStart) ));
        }

        $users = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
            ->createQueryBuilder('po')
            ->join('po.createdBy', 'u')
            ->select('COUNT(po.id) as totalPoCreate')
            ->addSelect('u.id')
            ->where("po.createdBy = :id");
        if(!empty($data)) {
            $users->andWhere('po.createdDate >= :start');
            $users->andWhere('po.createdDate <= :end');
            $users->setParameter('start', $monthStart);
            $users->setParameter('end', $monthEnd);
        }
        $users->setParameter('id', $id);

        return $users->getQuery()->getSingleResult();
    }

    private function getUsersByReceiveItem($id, $data)
    {
        if(!empty($data)){
            $monthStart = $data['month'] .' 00:00:01';
            $monthEnd = date('Y-m-t 23:59:59',(strtotime ($monthStart) ));
        }

        $users = $this->getDoctrine()->getRepository('PmsCoreBundle:Receive')
            ->createQueryBuilder('r')
            ->join('r.receivedBy', 'u')
            ->select('COUNT(r.id) as totalReceive')
            ->addSelect('u.id')
            ->where("r.receivedBy = :id");
            if(!empty($data)) {
                $users->andWhere('r.receivedDate >= :start');
                $users->andWhere('r.receivedDate <= :end');
                $users->setParameter('start', $monthStart);
                $users->setParameter('end', $monthEnd);
            }
        $users->setParameter('id', $id);

        return $users->getQuery()->getSingleResult();
    }

    public function userActivitiesAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $getUsersByPrCreate = $this->getUserByPrClaim($data);

        $totalPr = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getPrTotal($data);

        $rows = "";

        $i = 1;
        foreach($getUsersByPrCreate as $key => $row ){
            $index = $row['id'] . $row['username'];
            $getUsersByPrCreate[$key][$index] = $this->getPrCreateByUser($row['id'], $data);
            $getUsersByPrClaim[$key][$index] = $this->getPriClaimByUser($row['id'], $data);
            $getUsersByPoCreate[$key][$index] = $this->getPoCreateByUser($row['id'], $data);
            $getUsersByReceiveItem[$key][$index] = $this->getUsersByReceiveItem($row['id'], $data);
            $getPrClimAvgDay[$key][$index] = $this->getPrClimAvgDay($row['id'], $data);
            $getPoAvgDay[$key][$index] = $this->getPoAvgDay($row['id'], $data);
            $getReceiveAvg[$key][$index] = $this->getReceiveAvg($row['id'], $data);

            if(!empty($getPrClimAvgDay[$key][$index])){ $getPrClimAvg = $getPrClimAvgDay[$key][$index]; }else{ $getPrClimAvg = 0; }
            if(!empty($getPoAvgDay[$key][$index])){ $getPoAvg = $getPoAvgDay[$key][$index]; }else{ $getPoAvg = 0; }
            if(!empty($getReceiveAvg[$key][$index])){ $receiveAvg = $getReceiveAvg[$key][$index]; }else{ $receiveAvg = 0; }

            $rows .="<tr>";
            $rows .="<td class='numeric'>".$i."</td>";
            $rows .="<td class='numeric'>".$row['username']."</td>";
            $rows .="<td class='numeric'>".$totalPr."</td>";
            $rows .="<td class='numeric'>".$getUsersByPrCreate[$key][$index]['totalPrCreate']."</td>";
            $rows .="<td class='numeric'>".$getUsersByPrClaim[$key][$index]['totalPriClaim']."</td>";
            $rows .="<td class='numeric'>".$english_format_number = number_format($getPrClimAvg, 2, '.', '')."</td>";
            $rows .="<td class='numeric'>".$getUsersByPoCreate[$key][$index]['totalPoCreate']."</td>";
            $rows .="<td class='numeric'>".$english_format_number = number_format($getPoAvg, 2, '.', '')."</td>";
            $rows .="<td class='numeric'>".$getUsersByReceiveItem[$key][$index]['totalReceive']."</td>";
            $rows .="<td class='numeric'>".$english_format_number = number_format($receiveAvg, 2, '.', '')."</td>";
            $rows .="</tr>";

            $i = $i + 1;
        }


        return $this->render('PmsReportBundle:Report:userActivitiesReport.html.twig', array(
            'form' => $form->createView(),
            'users' => $getUsersByPrCreate,
            'data' => $rows,
        ));
    }

    public function userActivitiesExcelAction(Request $request)
    {
        list($form, $data) = $this->userActivitiesSearchForm($request);

        $getUsersByPrCreate = $this->getUserByPrClaim($data);

        $totalPr = $this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseRequisition')->getPrTotal($data);

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Username',
            'C9'=>'TotalPr',
            'D9'=>'TotalPrCreate',
            'E9'=>'TotalClaim',
            'F9'=>'ClaimAvg',
            'G9'=>'TotalPoCreate',
            'H9'=>'POCreateDelayAv',
            'I9'=>'ReceiveItem',
            'J9'=>'ReceiveDelayAyg',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'userActivities'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("C5", 'User Activities');

        if(!empty($getUsersByPrCreate)){
            $indexCount = 11;
            $count = 1;
            foreach($getUsersByPrCreate as $key => $row ){

                $index = $row['id'] . $row['username'];
                $getUsersByPrCreate[$key][$index] = $this->getPrCreateByUser($row['id'], $data);
                $getUsersByPrClaim[$key][$index] = $this->getPriClaimByUser($row['id'], $data);
                $getUsersByPoCreate[$key][$index] = $this->getPoCreateByUser($row['id'], $data);
                $getUsersByReceiveItem[$key][$index] = $this->getUsersByReceiveItem($row['id'], $data);
                $getPrClimAvgDay[$key][$index] = $this->getPrClimAvgDay($row['id'], $data);
                $getPoAvgDay[$key][$index] = $this->getPoAvgDay($row['id'], $data);
                $getReceiveAvg[$key][$index] = $this->getReceiveAvg($row['id'], $data);

                if(!empty($getPrClimAvgDay[$key][$index])){ $getPrClimAvg = $getPrClimAvgDay[$key][$index]; }else{ $getPrClimAvg = 0; }
                if(!empty($getPoAvgDay[$key][$index])){ $getPoAvg = $getPoAvgDay[$key][$index]; }else{ $getPoAvg = 0; }
                if(!empty($getReceiveAvg[$key][$index])){ $receiveAvg = $getReceiveAvg[$key][$index]; }else{ $receiveAvg = 0; }

                $objPHPExcel->getActiveSheet()->setCellValue("A".$indexCount, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$indexCount, $row['username']);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$indexCount, $totalPr);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$indexCount, $getUsersByPrCreate[$key][$index]['totalPrCreate']);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$indexCount, $getUsersByPrClaim[$key][$index]['totalPriClaim']);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$indexCount, $english_format_number = number_format($getPrClimAvg, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue("G".$indexCount, $getUsersByPoCreate[$key][$index]['totalPoCreate']);
                $objPHPExcel->getActiveSheet()->setCellValue("H".$indexCount, $english_format_number = number_format($getPoAvg, 2, '.', ''));
                $objPHPExcel->getActiveSheet()->setCellValue("I".$indexCount, $getUsersByReceiveItem[$key][$index]['totalReceive']);
                $objPHPExcel->getActiveSheet()->setCellValue("J".$indexCount, $english_format_number = number_format($receiveAvg, 2, '.', ''));

                $indexCount++;
                $count++;
            }

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        } else{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'Export');
        $objWriter->save($temp_file);
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition('attachment', $export_file_name);

        return $response;
    }

    public function excelSheetSet($header_arrays)
    {
        $redArr = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'f5f5f5')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '000000'),
                'size'  => 11,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );
        $objPHPExcel = new PHPExcel();

        //header set
        foreach($header_arrays as $key => $header_array ){

            $objPHPExcel->getActiveSheet()->setCellValue($key, $header_array);
            $objPHPExcel->getActiveSheet()->getStyle($key)->applyFromArray($redArr);
            $objPHPExcel->getActiveSheet()->getColumnDimension($key[0])->setWidth(22);
            $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(15);
        }

        return $objPHPExcel;
    }

    private function getPrClimAvgDay($id, $data)
    {
        if(!empty($data) && !empty($id)){
            $month = $data['month'];
            $monthStart = date('Y-m-01 00:00:01',(strtotime ($month) ));
            $monthEnd = date('Y-m-t 23:59:59',(strtotime ($month) ));

        $sql = "SELECT AVG(DATEDIFF(pri.claimed_date,pr.approved_date_category_head_two)) as claimedAvg FROM
          purchase_requisition_items as pri
          INNER JOIN purchase_requisitions as pr ON (pri.purchase_requisitions = pr.id)
          WHERE pri.claimed_by = $id AND pr.approved_date_category_head_two >= '$monthStart' AND pr.approved_date_category_head_two <= '$monthEnd' ";

        $result = $this->getDoctrine()->getConnection()->fetchAll($sql);

        return $result[0]['claimedAvg'];
        }
    }

    private function getPoAvgDay($id, $data)
    {
        if(!empty($data) && !empty($id)){
            $month = $data['month'];
            $monthStart = date('Y-m-01 00:00:01',(strtotime ($month) ));
            $monthEnd = date('Y-m-t 23:59:59',(strtotime ($month) ));

        $sql = "SELECT AVG(DATEDIFF(po.created_date,pri.claimed_date)) as poAvg FROM
          purchase_order_items as poi
          LEFT JOIN purchase_orders as po ON (poi.purchase_orders = po.id)
          LEFT JOIN purchase_requisition_items as pri ON (poi.purchase_requisition_item = pri.id)
          WHERE po.created_by = $id AND pri.claimed_date >= '$monthStart' AND pri.claimed_date <= '$monthEnd' ";

        $result = $this->getDoctrine()->getConnection()->fetchAll($sql);

        return $result[0]['poAvg'];
        }
    }

    private function getReceiveAvg($id, $data)
    {
        if(!empty($data) && !empty($id)){
            $month = $data['month'];
            $monthStart = date('Y-m-01 00:00:01',(strtotime ($month) ));
            $monthEnd = date('Y-m-t 23:59:59',(strtotime ($month) ));

            $sql = "SELECT AVG(DATEDIFF(r.received_date,po.approved_three_date)) as receiveAvg FROM
          received_items as ri
          LEFT JOIN receives as r ON (ri.receives = r.id)
          LEFT JOIN purchase_order_items as poi ON (ri.purchase_order_items = poi.id)
          LEFT JOIN purchase_orders as po ON (poi.purchase_orders = po.id)
          WHERE r.received_by = $id AND r.received_date >= '$monthStart' AND r.received_date <= '$monthEnd' ";

            $result = $this->getDoctrine()->getConnection()->fetchAll($sql);

            return $result[0]['receiveAvg'];
        }
    }
}