<?php
namespace TrainingCompany\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Entity\Survey;

class DefaultController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/", name="_home")
     * @Method("GET")
     */
    public function homeAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:home.html.twig');
    }

    /**
     * Fejlside til præsentation af tekniske fejl
     * @Route("/survey/error", name="_error")
     */
    public function errorAction(Request $request) {
        $session = $request->getSession();
        $error = $session->get('error');
        return $this->render('TrainingCompanyQueryBundle:Default:error.html.twig', array('error' => $error));
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/load", name="_load")
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
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/test", name="_test")
     * @Method("GET")
     */
    public function testAction() {
        $queryBuilder = new QueryBuilderFactory();
        $survey = $queryBuilder->getTemplate('QueryForm.yml');
        return $this->render('TrainingCompanyQueryBundle:Default:home.html.twig');
    }
}
