<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSchema;
use TrainingCompany\QueryBundle\Entity\Configuration;

class TemplateController extends Controller
{
    /**
     * Add new template
     * @Route("/admin/add/template", name="_admin_template_add")
     * @Template("TrainingCompanyQueryBundle:Admin:edittemplate.html.twig")
     */
    public function addAction(Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $schema = new QSchema();
        $form = $this->makeForm($schema, 'add');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($form->isValid()) {
            $qschemas = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('name' => $schema->getName()));
            if ($qschemas) {
                $form->addError(new FormError($this->get('translator')->trans('FORM.USER.NAMEEXIST', array(), 'admin')));
            }
            else {
                $em->persist($schema);
                $em->flush();
                return $this->redirect($returnUrl);
            }
        }
        return array('form' => $form->createView(), 'action' => 'add', 'schema' => $schema);
    }
    
   /**
     * Change template information
     * @Route("/admin/chg/template/{id}", name="_admin_template_chg")
     * @Template("TrainingCompanyQueryBundle:Admin:edittemplate.html.twig")
     */
    public function chgAction($id, Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $id));

        $form = $this->makeForm($schema, 'chg');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($this->checkForm($form, $schema)) {
            $qschemas = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('name' => $schema->getName()));
            if ($qschemas && $qschemas->getId() != $schema->getId()) {
                $form->addError(new FormError($this->get('translator')->trans('FORM.USER.NAMEEXIST', array(), 'admin')));
            }
            else {
                $em->flush();
                return $this->redirect($returnUrl);
            }
        }
        return array('form' => $form->createView(), 'action' => 'chg', 'schema' => $schema);
    }
    
   /**
     * Delete template information
     * @Route("/admin/del/template/{id}", name="_admin_template_del")
     * @Template("TrainingCompanyQueryBundle:Admin:edittemplate.html.twig")
     */
    public function delAction($id, Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $id));

        $form = $this->makeForm($schema, 'del');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($form->isValid()) {
            $em->remove($schema);
            $em->flush();
            return $this->redirect($returnUrl);
        }
        return array('form' => $form->createView(), 'action' => 'del', 'schema' => $schema);
    }

    private function makeForm(QSchema $schema, $action) {
        $formDef = $this->createFormBuilder($schema);
        $formDef->add('name', 'text', array('label' => 'FORM.USER.NAME', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('email', 'text', array('label' => 'FORM.USER.EMAIL', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('signer', 'text', array('label' => 'FORM.USER.SIGNER', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('sender', 'text', array('label' => 'FORM.USER.SENDER', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('cancel', 'submit', array('label' => 'FORM.USER.CANCEL.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'buttontype' => 'btn btn-default',
                                                'icon' => 'fa fa-times'));
        $formDef->add('save', 'submit', array('label' => 'FORM.USER.SUBMIT.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-check'));
        return $formDef->getForm();
    }
    
    private function checkForm($form, QSchema $schema) {
        if ($form->isValid()) {
            if ($schema->getName() == null || trim($schema->getName()) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.USER.NONAME', array(), 'admin')));
                return false;
            }
            if ($schema->getEmail() == null || trim($schema->getEmail()) == '') {
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
