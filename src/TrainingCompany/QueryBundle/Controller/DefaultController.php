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
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = new QueryBuilderFactory();
        $template = $queryBuilder->getTemplate('BakerTillyForm.xml');
        $survey = new Survey();
        $survey->name = 'Baker Tilly 2';
        $survey->signer = 'Henrik Vrangbæk Thomsen';
        $survey->queryblocks = $template;
        $queryBuilder->saveTemplate($em, $survey);
        return $this->render('TrainingCompanyQueryBundle:Default:optout.html.twig');
    }
}
