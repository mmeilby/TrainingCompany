<?php
namespace TrainingCompany\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class TestController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/test")
     */
    public function homeAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:home.html.twig');
    }

    /**
     * Fejlside til præsentation af tekniske fejl
     * @Route("/test/error")
     */
    public function errorAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:error.html.twig', array('error' => 'Her står en beskrivelse af en fejl.'));
    }
    
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/test/end")
     */
    public function endAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:end.html.twig');
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/test/finish")
     */
    public function finishAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig');
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/test/optout")
     */
    public function optAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:optout.html.twig');
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/test/timeout")
     */
    public function timeoutAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/test/invalid")
     */
    public function invalidAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/test/feedback_ty")
     */
    public function fbtyAction() {
        return $this->render('TrainingCompanyQueryBundle:Default:feedback_ty.html.twig');
    }

}
