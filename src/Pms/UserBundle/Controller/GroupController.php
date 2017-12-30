<?php

namespace Pms\UserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Pms\UserBundle\Entity\Group;
use FOS\UserBundle\Controller\GroupController as Controller;
use FOS\UserBundle\Event\FilterGroupResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseGroupEvent;
use FOS\UserBundle\Event\GroupEvent;
use FOS\UserBundle\FOSUserEvents;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupController extends Controller
{
	/**
	 * @param Request $request
	 * @param Group   $group
	 *
	 * @return null|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function editByIdAction(Request $request, Group $group)
	{
		/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
		$dispatcher = $this->container->get('event_dispatcher');

		$event = new GetResponseGroupEvent($group, $request);
		$dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_INITIALIZE, $event);

		if (null !== $event->getResponse()) {
			return $event->getResponse();
		}

		/** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
		$formFactory = $this->container->get('fos_user.group.form.factory');

		$form = $formFactory->createForm();
		$form->setData($group);

		if ($request->isMethod('POST')) {
			$form->bind($request);

			if ($form->isValid()) {
				/** @var $groupManager \FOS\UserBundle\Model\GroupManagerInterface */
				$groupManager = $this->container->get('fos_user.group_manager');

				//$event = new FormEvent($form, $request);
				//$dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_SUCCESS, $event);

				$groupManager->updateGroup($group);

				if (null === $response = $event->getResponse()) {
					$this->get('session')->getFlashBag()->add(
						'success-status',
						'Group Updated Successfully!'
					);

					$url      = $this->container->get('router')->generate('fos_user_group_list');
					$response = new RedirectResponse($url);

				}

				//$dispatcher->dispatch(FOSUserEvents::GROUP_EDIT_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

				return $response;
			}
		}

		return $this->container->get('templating')->renderResponse('UserBundle:Group:edit.html.twig', array(
			'form'      => $form->createview(),
			'group'  => $group,
		));
	}

	/**
	 *  @JMS\Secure(roles="ROLE_GROUP_DELETE")
	 */
	public function deleteByIdAction(Request $request, Group $group)
	{
		$this->container->get('fos_user.group_manager')->deleteGroup($group);

		$response = new RedirectResponse($this->container->get('router')->generate('fos_user_group_list'));

		/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
		//$dispatcher = $this->container->get('event_dispatcher');
		//$dispatcher->dispatch(FOSUserEvents::GROUP_DELETE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

		$this->get('session')->getFlashBag()->add(
			'success-status',
			'Group Deleted Successfully!'
		);

		return $response;
	}

    /**
     * Show the new form
     */
    public function newAction(Request $request)
    {
        /** @var $groupManager \FOS\UserBundle\Model\GroupManagerInterface */
        $groupManager = $this->get('fos_user.group_manager');
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.group.form.factory');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $group = $groupManager->createGroup('');

        $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_INITIALIZE, new GroupEvent($group, $request));

        $form = $formFactory->createForm();
        $form->setData($group);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_SUCCESS, $event);

            $groupManager->updateGroup($group);

            if (null === $response = $event->getResponse()) {
                $this->get('session')->getFlashBag()->add(
                    'success-status',
                    'Group Created Successfully!'
                );

                $url      = $this->container->get('router')->generate('fos_user_group_list');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

            return $response;
        }

        return $this->container->get('templating')->renderResponse('UserBundle:Group:new.html.twig', array(
            'form'      => $form->createview(),
        ));
    }

    public function get($name)
	{
		return $this->container->get($name);
	}
}