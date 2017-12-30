<?php

namespace Pms\UserBundle\Controller;

use Pms\UserBundle\Form\SearchForm\UserSearchType;
use Pms\UserBundle\Form\Type\UserFormEditType;
use Pms\UserBundle\Form\Type\UserFormPasswordEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pms\UserBundle\Entity\User;
use Pms\UserBundle\Form\Type\UserNotificationFormType;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageUserController extends Controller
{
    public function indexAction(Request $request, $page)
    {
        $formSearch = $this->createForm(new UserSearchType());

        $request->query->set('page', $page);

        $userID = 0;
        return $this->render('UserBundle:Users:index.html.twig', array(
            'pagination' =>  $this->getPaginateData(),
            'page' =>  $page,
            'user' =>  $userID,
            'formSearch' => $formSearch->createView(),
        ));
    }

    private function getPaginateData()
    {
        $query = $this->getUserManager()->getUserQuery();

        $paginator = $this->get('knp_paginator');

        return $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1),
            10,
            array('distinct'=>false)
        );
    }

    public function createAction(Request $request)
    {
        $userManager = $this->getUserManager();

        $user = new User();//$userManager->createUser();

	    $service = $this->get('pms_user.registration.form.type');

        $form = $this->createForm($service, $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $userManager->updateUser($user);

                $this->get('session')->getFlashBag()->add(
                    'user-success',
                    'User Created Successfully!'
                );

                return $this->redirect($this->generateUrl('pms_user_manager'));
            }
        }

        return $this->render('UserBundle:Users:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction(Request $request, User $user)
    {
        $userManager = $this->getUserManager();

//	    $service = $this->get('pms_user.registration.form.type');
	    $form = $this->createForm(new UserFormEditType(), $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $user->isEnabled(true);
                $userManager->updateUser($user);

                $this->get('session')->getFlashBag()->add(
                    'user-success',
                    'User Updated Successfully!'
                );

                if($_POST['urlRef']){

                    return $this->redirect($_POST['urlRef']);
                }else{

                    return $this->redirect($this->generateUrl('pms_user_manager'));
                }
            }
        }

        return $this->render('UserBundle:Users:edit.html.twig', array(
            'form'      => $form->createView(),
            'user' => $user,
        ));
    }

    public function userPasswordEditAction(Request $request, User $user)
    {
        $userManager = $this->getUserManager();

        $form = $this->createForm(new UserFormPasswordEditType(), $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $user->isEnabled(true);
                $userManager->updateUser($user);

                $this->get('session')->getFlashBag()->add(
                    'user-success',
                    'User Updated Successfully!'
                );

                if($_POST['urlRef']){

                    return $this->redirect($_POST['urlRef']);
                }else{

                    return $this->redirect($this->generateUrl('pms_user_manager'));
                }
            }
        }

        return $this->render('UserBundle:Users:passwordEdit.html.twig', array(
            'form'      => $form->createView(),
            'user_id'   => $user->getId(),
            'user_name' => $user->getUsername()
        ));
    }

    /**
     * @JMS\Secure(roles="ROLE_USER_DELETE")
     */
    public function deleteAction(Request $request, User $user)
    {
        $userManager = $this->getUserManager();

        $submittedData = $request->request->all();

        if($user->getId() != $submittedData['id']){
            throw $this->createNotFoundException('Page not found');
        }

        if ('POST' === $request->getMethod()) {

            if ($this->isCsrfTokenValid('delete_action_' . $submittedData['id'], $submittedData['_token'])) {

                $this->deleteAvatar($user);
                $userManager->deleteUser($user);

                $this->get('session')->getFlashBag()->add(
                    'user-success',
                    'User Deleted Successfully!'
                );
            } else {
                $this->get('session')->getFlashBag()->add(
                    'user-error',
                    'Delete failed! Please Resubmit your form'
                );
            }

            return $this->redirect($this->generateUrl('pms_user_manager'));
        }

        throw $this->createNotFoundException('Page not found');
    }

    public function checkDuplicateAction(Request $request, $type)
    {
        $usernameOrEmail = $request->request->get("manage_user[$type]", NULL, TRUE);

        $userManager = $this->getUserManager();

        $user = $userManager->findUserByUsernameOrEmail($usernameOrEmail);

        if ($user && $user->getId() != $request->request->get("user_id")) {
            $response = 'false';
        } else {
            $response = 'true';
        }

        return new Response($response);
    }

    public function avatarAction(Request $request)
    {

        if($request->getMethod() == 'POST')
        {
            $user = $this->getUser();
            $csrf = 'update_avatar_action_' . $user->getId();

            if ($this->isCsrfTokenValid($csrf, $request->request->get('_token'))) {

                if ($this->uploadAvatar($request->files->get('avatar'), $user)) {

                    $this->get('session')->getFlashBag()->add(
                        'user-success',
                        'New Avatar saved Successfully!'
                    );

                    $this->getUserManager()->updateUser($user);
                }
            }
        }

        return $this->render('DocudexUserBundle:Avatar:edit.html.twig');
    }

    public function notificationAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(new UserNotificationFormType(), $user);

        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $userManager = $this->getUserManager();
                $userManager->updateUser($user);

                $this->getEventDispatcher()->dispatch(DocudexUserEvent::USER_NOTIFICATION_SETTINGS_UPDATED, new UserEvent($user, $request));

                $this->get('session')->getFlashBag()->add(
                    'user-success',
                    'Settings Updated Successfully!'
                );
            }
        }

        return $this->render('DocudexUserBundle:Profile:notification_settings.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * @JMS\Secure(roles="ROLE_MANAGE_USERS")
     */
    public function changeUserPasswordAction(Request $request, User $user)
    {
        $val = $user->getPassword();

        if(empty($val)) {
            throw $this->createNotFoundException('Page not found');
        }

        $plainPassword = $this->isValidFormDataForChangePassword($request->request, $user->getId());

        $response = $this->redirect($this->getUserListUrl($request->request->get('page')));

        if ($plainPassword) {
            $this->getUserManager()->changeUserPassword($user, $plainPassword);

            $this->getEventDispatcher()->dispatch(
                FOSUserEvents::CHANGE_PASSWORD_COMPLETED,
                new FilterUserResponseEvent($user, $request, $response)
            );

            $this->get('session')->getFlashBag()->add(
                'user-success',
                'Password updated successfully!!'
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'user-error',
                'Password updated failed! Please Resubmit your form'
            );
        }

        return $response;
    }

    protected function isValidFormDataForChangePassword(ParameterBag $request, $userId)
    {
        if (!$this->isCsrfTokenValid('password_change_' . $userId, $request->get('_token'))) {
            return FALSE;
        }

        $plainPassword = $request->get('password');
        $confirmPassword = trim($request->get('confirm_password'));

        if ($plainPassword === $confirmPassword && strlen($confirmPassword) >= 6) {
            return $plainPassword;
        }

        return FALSE;
    }

    protected function getUserListUrl($page = 1)
    {
        return $this->container->get('router')->generate(
            'pms_user_manager',
            array('page'=>$page)
        );
    }

    private function uploadAvatar($file, User $user)
    {
        if(empty($file)) {
           return false;
        }

        $uploader = $this->get('pms.core.service.uploader');

        $uploadedFileInfo = $uploader
            ->setUploadPathKey('user.profile.image')
            ->upload($file);

        if ($uploadedFileInfo) {

            $this->deleteAvatar($user, $uploader->getUploadPath());
            $user->setAvatar($uploadedFileInfo['filename']);

            return $uploadedFileInfo;
        }

        return false;
    }

    private function deleteAvatar(User $user, $baseDire = null)
    {
        $uploadDir = $this->getAvatarBaseDirectory($baseDire);

        if ($user->getAvatar() != "" && file_exists($uploadDir . $user->getAvatar())) {
            unlink($uploadDir . $user->getAvatar());
        }
    }

    private function getAvatarBaseDirectory($baseDire = null)
    {
        if($baseDire == null) {
            $pathConfigurator = $this->get('pms.core.service.path.configurator');
            $baseDire = $pathConfigurator->getPathFromKey('user.profile.image', true);
        }

        if (substr($baseDire, -1, 1) != DIRECTORY_SEPARATOR) {
            $baseDire = $baseDire . DIRECTORY_SEPARATOR;
        }

        return $baseDire;
    }

	private function getUserManager()
	{
		return $this->get('pms.user_manager');
	}
}