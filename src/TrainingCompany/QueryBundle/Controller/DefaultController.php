<?php
namespace TrainingCompany\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/error", name="_error")
     */
    public function errorAction(Request $request) {
        $session = $request->getSession();
        $error = $session->get('error');
        return $this->render('TrainingCompanyQueryBundle:Default:error.html.twig', array('error' => $error));
    }
}
