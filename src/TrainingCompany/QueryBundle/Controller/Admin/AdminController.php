<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use JMS\SecurityExtraBundle\Annotation\Secure;
use TrainingCompany\QueryBundle\Entity\Admin\NewPerson;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;
use TrainingCompany\QueryBundle\Controller\QueryBuilderFactory;

class AdminController extends Controller
{
    /**
     * Administrationsside
     * @Route("/admin", name="_admin")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:dashboard.html.twig")
     */
    public function adminAction(Request $request) {
        $choices = array();
        $em = $this->getDoctrine()->getManager();
        $qschema = $em->getRepository(Configuration::SchemaRepo())->findAll();
        if ($qschema) {
            foreach ($qschema as $schema) {
                $choices[$schema->getId()] = $schema->getName();
            }
        }
        $formData = new NewPerson();
        $formDef = $this->createFormBuilder($formData);
        $formDef->add('survey', 'choice', array('label' => 'Spørgeskema', 'required' => false, 'choices' => $choices, 'empty_value' => 'Vælg...'));
        $formDef->add('view', 'submit', array('label' => 'Vis',
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-envelope-o'));
        $form = $formDef->getForm();

        $session = $request->getSession();
        $schemaid = $session->get('dashboard_schema', 0);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $schemaid = $formData->survey;
                $session->set('dashboard_schema', $schemaid);
            }
        }

        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $schemaid));
        return array('form' => $form->createView(), 'schema' => $schema);
    }
}
