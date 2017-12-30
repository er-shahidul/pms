<?php

namespace Pms\DocumentBundle\Controller;

use Doctrine\ORM\Repository;
use Pms\DocumentBundle\Entity\Document;
use Pms\DocumentBundle\Form\DocumentType;
use Pms\DocumentBundle\Form\SearchForm\DocumentSearchType;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Document controller.
 *
 */
class DocumentController extends Controller
{
    public function indexAction(Request $request, $status = 'invoice')
    {

        $documentRepository = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document');

        $form = new DocumentSearchType();

        $data = $request->query->get($form->getName());
        $document = $request->query->get('document');
        if(!empty($document)){
        $data['document'] = $document;
        }
        switch (true) {
            case isset($data['document']) && isset($data['user']) && isset($data['po']):
                $documents = $this->paginate($documentRepository->getDocumentSearch($this->getUser(), $data['document'], $data['user'],$data['po'], $status));
                break;
            case isset($data['document']) && empty($data['user']) && isset($data['po']):
                $documents = $this->paginate($documentRepository->getDocumentSearch($this->getUser(), $data['document'], 0, $data['po'],$status));
                break;
            case isset($data['user']) && empty($data['document']) && isset($data['po']):
                $documents = $this->paginate($documentRepository->getDocumentSearch($this->getUser(), 0, $data['user'],$data['po'], $status));
                break;
            default:
                $documents = $this->paginate($documentRepository->getDocumentSearch($this->getUser(), 0, 0,0, $status));

        }

        $formSearch = $this->createForm($form, $data);

        return $this->render('PmsDocumentBundle:Document:home.html.twig', array(
            'documents' => $documents,
            'formSearch' => $formSearch->createView(),
            'status' => $status,
        ));
    }

    public function attachmentViewAction(Request $request, Document $document, $index)
    {
        if(null !== $response = $this->checkAttachFileByIndex($document, $index)) {
            return $response;
        }

        return $this->render('PmsDocumentBundle:Document:viewer.html.twig', array(
            'd' => $document,
            'path' => $document->getPath(),
        ));
    }

    private function checkAttachFileByIndex(Document $document, $index)
    {
        if (null == $fileName = $document->getAbsolutePathByIndex($index)) {
            return null;
        }

        $fileSize = filesize($fileName);

        if ($fileSize > 2300000) {
            return new BinaryFileResponse($fileName);
        }
    }

    public function detailsAction($id)
    {
        $document = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($id);

        return $this->render('PmsDocumentBundle:Document:details.html.twig', array(
            'document' => $document,
            'id' => $id,
        ));
    }

    public function uploadFileAction(Request $request)
    {
        $document = new Document();

        $form = $this->createForm(new DocumentType(), $document);
        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $document->setUploadedBy($this->getUser());
                $document->setUploadedDate(new \DateTime());

                $document->setPurchaseOrder($this->getDoctrine()->getRepository('PmsCoreBundle:PurchaseOrder')
                                                     ->find($document->getPurchaseOrder()));

                $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->create($document);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'File Successfully Upload'
                );

                return $this->redirect($this->generateUrl('document'));
            }
        }

        return $this->render('PmsDocumentBundle:Document:form.html.twig', array(
            'form' => $form->createView(),
        ));
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

    public function documentTitleAutoCompleteAction(Request $request){

        $documentTitleQuery = array();
        $documentTitle = $request->query->get('q', null);

        $document = $request->query->get('document',  null);

        if ($documentTitle) {
            $documentTitleQuery = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')
                ->documentTitleAutoNumber($documentTitle, $this->getUser());

        }
        elseif ($document) {
            $doc = $this->getDoctrine()->getRepository('PmsDocumentBundle:Document')->find($document);
            $documentTitleQuery = array('id' => $doc->getId(), 'text' => $doc->getTitle());
        }

        return new JsonResponse($documentTitleQuery);
    }
}
