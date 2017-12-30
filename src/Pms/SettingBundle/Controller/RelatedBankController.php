<?php

namespace Pms\SettingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Pms\SettingBundle\Entity\RelatedBank;
use Pms\SettingBundle\Form\RelatedBankType;
use Pms\CoreBundle\Controller\BaseController;

/**
 * RelatedBank controller.
 *
 */
class RelatedBankController extends BaseController
{
    public function indexAction()
    {
        $relatedBanks = $this->paginate($this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')->findAll());

        return $this->render('PmsSettingBundle:RelatedBank:home.html.twig', array(
            'relatedBanks' => $relatedBanks,
        ));
    }

    public function newAction(Request $request)
    {
        $relatedBank = new RelatedBank();

        $form = $this->createForm(new RelatedBankType(), $relatedBank);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $relatedBank->setCreatedBy($this->getUser());
                $relatedBank->setCreatedDate(new \DateTime());

                $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')->create($relatedBank);

                $msg = 'Related Bank Successfully Inserted';
                $this->successMessage($msg);

                return $this->redirect($this->generateUrl('relatedbank'));
            }
        }

        return $this->render('PmsSettingBundle:RelatedBank:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function updateAction(Request $request, RelatedBank $relatedBank)
    {
        $form = $this->createForm(new RelatedBankType(), $relatedBank);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')->update($relatedBank);

                $msg = 'Related Bank Successfully Updated';
                $this->successMessage($msg);

                return $this->redirect($this->generateUrl('relatedbank'));
            }
        }

        return $this->render('PmsSettingBundle:RelatedBank:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(RelatedBank $relatedBank)
    {
        $this->getDoctrine()->getRepository('PmsSettingBundle:RelatedBank')
            ->delete($relatedBank);

        $massage = 'Related Bank Successfully Deleted';
        $this->successMessage($massage);

        return $this->redirect($this->generateUrl('relatedbank'));
    }
}
