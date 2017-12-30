<?php

namespace Pms\SettingBundle\Controller;

use Doctrine\ORM\Repository;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use Pms\SettingBundle\Entity\Project;
use Pms\SettingBundle\Form\ProjectType;

use Pms\SettingBundle\Form\SearchForm\ProjectSearchType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Project controller.
 *
 */
class ProjectController extends Controller
{
    public function projectSearchForm($request)
    {
        $form = new ProjectSearchType();
        $data = $request->get($form->getName());
        return array($form, $data);
    }

    public function indexAction(Request $request, $status = 'active')
    {
        list($form, $data) = $this->projectSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $projectRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Project');
        $projectName = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->findOneBy(array('isHeadOffice'=>1));


        switch (true) {
            case isset($data['project']) && !empty($data['project']):
                $projects = $this->paginate($projectRepository->getProjectSearch($data['project'], 0, 0, $status));
                break;
            case isset($data['projectType']) && !empty($data['projectType']):
                $projects = $this->paginate($projectRepository->getProjectSearch(0, $data['projectType'], 0, $status));
                break;
            case isset($data['area']) && !empty($data['area']):
                $projects = $this->paginate($projectRepository->getProjectSearch(0, 0, $data['area'], $status));
                break;
            default:
                $projects = $this->paginate($projectRepository->getProjectSearch(0, 0, 0, $status));
        }

        return $this->render('PmsSettingBundle:Project:home.html.twig', array(
            'projects' => $projects,
            'headProjectName'=>$projectName->getProjectName(),
            'form' => $form->createView(),
            'status' => $status,
        ));
    }

    public function excelProjectsListAction(Request $request, $status = 'active')
    {
        list($form, $data) = $this->projectSearchForm($request);

        $form = $this->createForm($form);
        $form->submit($data);

        $projectRepository = $this->getDoctrine()->getRepository('PmsSettingBundle:Project');

        switch (true) {
            case isset($data['project']) && !empty($data['project']):
                $projects = $projectRepository->getProjectSearch($data['project'], 0, 0, $status);
                break;
            case isset($data['projectType']) && !empty($data['projectType']):
                $projects = $projectRepository->getProjectSearch(0, $data['projectType'], 0, $status);
                break;
            case isset($data['area']) && !empty($data['area']):
                $projects = $projectRepository->getProjectSearch(0, 0, $data['area'], $status);
                break;
            default:
                $projects = $projectRepository->getProjectSearch(0, 0, 0, $status);
        }

        $projects = $projects->getQuery()->getResult();

        // Export LRP wise data
        $header_arrays = array(
            'A9'=>'S/L',
            'B9'=>'Project Name',
            'C9'=>'Project Head',
            'D9'=>'Type',
            'E9'=>'Area',
            'F9'=>'Address',
        );

        $objPHPExcel = $this->excelSheetSet($header_arrays);

        $export_file_name = 'ProjectList'.'.xlsx';

        $objPHPExcel->getActiveSheet()->setCellValue("D5", 'Project List');
        if(!empty($projects)){
            $index = 11;
            $count = 1;
            foreach($projects as $project){

                $projectName = $project->getProjectName() ? $project->getProjectName() : "...";
                $projectHead = $project->getProjectHead() ? $project->getProjectHead()->getUsername() : "...";
                $type = $project->getProjectCategory() ? $project->getProjectCategory()->getProjectCategoryName() : "...";
                $area = $project->getProjectArea() ? $project->getProjectArea()->getAreaName() : "...";
                $address = $project->getAddress() ? $project->getAddress() : "...";

                $objPHPExcel->getActiveSheet()->setCellValue("A".$index, $count);
                $objPHPExcel->getActiveSheet()->setCellValue("B".$index, $projectName);
                $objPHPExcel->getActiveSheet()->setCellValue("C".$index, $projectHead);
                $objPHPExcel->getActiveSheet()->setCellValue("D".$index, $type);
                $objPHPExcel->getActiveSheet()->setCellValue("E".$index, $area);
                $objPHPExcel->getActiveSheet()->setCellValue("F".$index, $address);

                $index++;
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

    public function addAction(Request $request)
    {
        $project = new Project();

        $isHeaOffice = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->findBy(array('isHeadOffice'=>1));
        $form = $this->createForm(new ProjectType($isHeaOffice), $project);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $project->setCreatedBy($this->getUser());
                $project->setCreatedDate(new \DateTime());
                $project->setStatus(1);

                $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->create($project);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Project Add Successfully'
                );

                return $this->redirect($this->generateUrl('project'));
            }
        }

        return $this->render('PmsSettingBundle:Project:form.html.twig', array(
            'isHeadOffice' =>$isHeaOffice,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Project $project)
    {
        $project->setStatus(0);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->update($project);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Project Successfully Deleted'
        );

        return $this->redirect($this->generateUrl('project'));
    }

    public function activeAction(Project $project)
    {
        $project->setStatus(1);
        $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->update($project);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Project Successfully Restored'
        );

        return $this->redirect($this->generateUrl('project'));
    }

    public function updateAction(Request $request, Project $project)
    {
        $isHeaOffice = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->findBy(array('isHeadOffice'=>1));
        $form = $this->createForm(new ProjectType($isHeaOffice), $project);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->update($project);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Project Add Successfully'
                );

                if($_POST['urlRef']){

                    return $this->redirect($_POST['urlRef']);
                }else{

                    return $this->redirect($this->generateUrl('project'));
                }
            }
        }

        return $this->render('PmsSettingBundle:Project:form.html.twig', array(
            'isHeadOffice' =>$isHeaOffice,
            'form' => $form->createView(),
        ));
    }

    public function checkAction(Request $request)
    {
        $projectName = $request->request->get('projectName');

        $projectNameCheck = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->findOneBy(
            array('projectName' => $projectName )
        );

        if ($projectNameCheck) {
            $return = array("responseCode" => 200, "project_name" => "Project name already exist.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } else {
            $return = array("responseCode" => '404', "project_name" => "Project name available.");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
    }

    public function projectFindAllAction(Request $request)
    {
        $projectName = $request->request->get('projectName');

        $project = $this->getDoctrine()->getRepository('PmsSettingBundle:Project')->find($projectName);

        if ($project) {
            $projectArr = array();
            $projectArr['projectAddress'] = $project->getAddress();
            $projectArr['projectCategory'] = $project->getProjectCategory()->getProjectCategoryName();
            $projectArr['projectArea'] = $project->getProjectArea()->getAreaName();
            $projectArr['projectHead'] = $project->getProjectHead()->getUsername();
            $projectArr['projectHeadFull'] = $project->getProjectHead()->getFullName();
            $projectArr['costCenterNumber'] = $project->getCostCenterNumber();

            $response = new Response(json_encode(array(
                'projectFindAll' => $projectArr
            )));

            $response->headers->set('Content-Type', 'application/json');
            return $response;
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