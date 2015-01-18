<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class LoadSchemaController extends Controller
{
    /**
     * Load schema into database
     * @Route("/admin/load", name="_load")
     * @Method("GET")
     */
    public function loadAction() {
        $queryBuilder = new QueryBuilderFactory();
        $survey = $queryBuilder->getTemplate('QueryForm.yml');
        $em = $this->getDoctrine()->getManager();
        $queryBuilder->saveTemplate($em, $survey);
        return $this->render('TrainingCompanyQueryBundle:Default:home.html.twig');
    }
    
    /**
     * Test schema for errors - no loading will be performed
     * @Route("/admin/test", name="_test")
     * @Method("GET")
     */
    public function testAction() {
        $queryBuilder = new QueryBuilderFactory();
        $queryBuilder->getTemplate('QueryForm.yml');
        return $this->render('TrainingCompanyQueryBundle:Default:home.html.twig');
    }
}
