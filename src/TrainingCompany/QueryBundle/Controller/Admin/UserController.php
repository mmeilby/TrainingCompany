<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Configuration;

class UserController extends Controller
{
    /**
     * Add new club attached user
     * @Route("/admin/add/user", name="_admin_user_add")
     * @Template("TrainingCompanyQueryBundle:Admin:edituser.html.twig")
     */
    public function addAction(Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $user = new QPersons();
        $form = $this->makeUserForm($user, 'add');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($form->isValid()) {
            $qpersons = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('email' => $user->getEmail()));
            if ($qpersons) {
                $form->addError(new FormError($this->get('translator')->trans('FORM.USER.NAMEEXIST', array(), 'admin')));
            }
            else {
                $user->setUsername($user->getEmail());
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($user->getEmail(), $user->getSalt());
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
                return $this->redirect($returnUrl);
            }
        }
        return array('form' => $form->createView(), 'action' => 'add', 'user' => $user);
    }
    
   /**
     * Change user information
     * @Route("/admin/chg/user/{userid}", name="_admin_user_chg")
     * @Template("TrainingCompanyQueryBundle:Admin:edituser.html.twig")
     */
    public function chgAction($userid, Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('id' => $userid));

        $form = $this->makeUserForm($user, 'chg');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($this->checkForm($form, $user)) {
            $qpersons = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('email' => $user->getEmail()));
            if ($qpersons && $qpersons->getId() != $user->getId()) {
                $form->addError(new FormError($this->get('translator')->trans('FORM.USER.NAMEEXIST', array(), 'admin')));
            }
            else {
                $em->flush();
                return $this->redirect($returnUrl);
            }
        }
        return array('form' => $form->createView(), 'action' => 'chg', 'user' => $user);
    }
    
   /**
     * Delete user information
     * @Route("/admin/del/{userid}", name="_admin_user_del")
     * @Template("TrainingCompanyQueryBundle:Admin:edituser.html.twig")
     */
    public function delAction($userid, Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('id' => $userid));

        $form = $this->makeUserForm($user, 'del');
        $form->handleRequest($request);
        
        /* @var $user QPersons */
        $thisuser = $this->get('security.context')->getToken()->getUser();
        // Check for "self destruction" - current user is not allowed to remove own profile 
        if ($thisuser && $thisuser instanceof QPersons && $thisuser->getId() == $user->getId()) {
            $form->addError(new FormError($this->get('translator')->trans('FORM.USER.CANNOTDELETESELF', array(), 'admin')));
        }
        else {
            if ($form->get('cancel')->isClicked()) {
                return $this->redirect($returnUrl);
            }
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($user);
                $em->flush();
                return $this->redirect($returnUrl);
            }
        }
        return array('form' => $form->createView(), 'action' => 'del', 'user' => $user);
    }

    private function makeUserForm(QPersons $user, $action) {
        $formDef = $this->createFormBuilder($user);
        $formDef->add('name', 'text', array('label' => 'FORM.USER.NAME', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('email', 'text', array('label' => 'FORM.USER.EMAIL', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('username', 'text', array('label' => 'FORM.USER.USERNAME', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('cancel', 'submit', array('label' => 'FORM.USER.CANCEL.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'buttontype' => 'btn btn-default',
                                                'icon' => 'fa fa-times'));
        $formDef->add('save', 'submit', array('label' => 'FORM.USER.SUBMIT.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-check'));
        return $formDef->getForm();
    }
    
    private function checkForm($form, QPersons $user) {
        if ($form->isValid()) {
            if ($user->getName() == null || trim($user->getName()) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.USER.NONAME', array(), 'admin')));
                return false;
            }
            if ($user->getEmail() == null || trim($user->getEmail()) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.USER.NOUSERNAME', array(), 'admin')));
                return false;
            }
            return true;
        }
        return false;
    }

    public function getReferer(Request $request) {
        if ($request->isMethod('GET')) {
            $returnUrl = $request->headers->get('referer');
            $session = $request->getSession();
            $session->set('icup.referer', $returnUrl);
        }
        else {
            $session = $request->getSession();
            $returnUrl = $session->get('icup.referer');
        }
        return $returnUrl;
    }
}
