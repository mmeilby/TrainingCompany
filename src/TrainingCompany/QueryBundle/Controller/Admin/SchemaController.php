<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSchema;
use TrainingCompany\QueryBundle\Entity\Configuration;

class SchemaController extends Controller
{
    /**
     * Add new schema
     * @Route("/admin/add/schema", name="_admin_schema_add")
     * @Template("TrainingCompanyQueryBundle:Admin:editschema.html.twig")
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
        if ($this->checkForm($form, $schema)) {
            $qschemas = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('name' => $schema->getName()));
            if ($qschemas) {
                $form->addError(new FormError($this->get('translator')->trans('FORM.SCHEMA.NAMEEXIST', array(), 'admin')));
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
     * Change schema information
     * @Route("/admin/chg/schema/{id}", name="_admin_schema_chg")
     * @Template("TrainingCompanyQueryBundle:Admin:editschema.html.twig")
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
                $form->addError(new FormError($this->get('translator')->trans('FORM.SCHEMA.NAMEEXIST', array(), 'admin')));
            }
            else {
                $em->flush();
                return $this->redirect($returnUrl);
            }
        }
        return array('form' => $form->createView(), 'action' => 'chg', 'schema' => $schema);
    }
    
   /**
     * Delete schema information
     * @Route("/admin/del/schema/{id}", name="_admin_schema_del")
     * @Template("TrainingCompanyQueryBundle:Admin:editschema.html.twig")
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
            $this->removeSchema($schema);
            return $this->redirect($returnUrl);
        }
        return array('form' => $form->createView(), 'action' => 'del', 'schema' => $schema);
    }

    private function makeForm(QSchema $schema, $action) {
        $formDef = $this->createFormBuilder($schema);
        $formDef->add('name', 'text', array('label' => 'FORM.SCHEMA.NAME.LABEL', 'help' => 'FORM.SCHEMA.NAME.HELP', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('invitation', 'text', array('label' => 'FORM.SCHEMA.INVITATION.LABEL', 'help' => 'FORM.SCHEMA.INVITATION.HELP', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('signer', 'text', array('label' => 'FORM.SCHEMA.SIGNER.LABEL', 'help' => 'FORM.SCHEMA.SIGNER.HELP', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('sender', 'text', array('label' => 'FORM.SCHEMA.SENDER.LABEL', 'help' => 'FORM.SCHEMA.SENDER.HELP', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('email', 'text', array('label' => 'FORM.SCHEMA.EMAIL.LABEL', 'help' => 'FORM.SCHEMA.EMAIL.HELP', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('cancel', 'submit', array('label' => 'FORM.SCHEMA.CANCEL.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'buttontype' => 'btn btn-default',
                                                'icon' => 'fa fa-times'));
        $formDef->add('save', 'submit', array('label' => 'FORM.SCHEMA.SUBMIT.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-check'));
        return $formDef->getForm();
    }
    
    private function checkForm($form, QSchema $schema) {
        if ($form->isValid()) {
            $noError = true;
            if ($schema->getName() == null || trim($schema->getName()) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.SCHEMA.NONAME', array(), 'admin')));
                $noError = false;
            }
            if ($schema->getEmail() == null || trim($schema->getEmail()) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.SCHEMA.NOEMAIL', array(), 'admin')));
                $noError = false;
            }
            return $noError;
        }
        return false;
    }

    private function removeSchema(QSchema $schema) {
        $em = $this->getDoctrine()->getManager();
        $em->createQuery(
            "delete from ".Configuration::ResponseRepo()." r ".
            "where r.qid in (select s.id from ".Configuration::SurveyRepo()." s where s.sid=:schema)")
                ->setParameter('schema', $schema->getId())
                ->getResult();
        $em->createQuery(
            "delete from ".Configuration::CommentRepo()." c ".
            "where c.qid in (select s.id from ".Configuration::SurveyRepo()." s where s.sid=:schema)")
                ->setParameter('schema', $schema->getId())
                ->getResult();
        $em->createQuery(
            "delete from ".Configuration::SurveyRepo()." s ".
            "where s.sid=:schema")
                ->setParameter('schema', $schema->getId())
                ->getResult();
        $em->createQuery(
            "delete from ".Configuration::DomainRepo()." d ".
            "where d.qbid in (select b.id from ".Configuration::BlockRepo()." b where b.sid=:schema)")
                ->setParameter('schema', $schema->getId())
                ->getResult();
        $em->createQuery(
            "delete from ".Configuration::BlockRepo()." b ".
            "where b.sid=:schema")
                ->setParameter('schema', $schema->getId())
                ->getResult();
        $em->remove($schema);
        $em->flush();
    }
    
    private function getReferer(Request $request) {
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
