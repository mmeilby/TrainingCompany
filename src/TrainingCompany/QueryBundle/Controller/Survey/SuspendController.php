<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Controller\Survey\SurveyController;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;

class SuspendController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/suspend", name="_suspend")
     * @Method("GET")
     */
    public function suspendAction(Request $request) {
        $session = $request->getSession();
        $qid = $session->get(SurveyController::$sessionSurveyId);
       	$page = $session->get(SurveyController::$sessionPage);
        if (!isset($qid) || !isset($page)) {
             return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $qsurveys = $em->getRepository(Configuration::SurveyRepo())->find($qid);
        if (!$qsurveys) {
            return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
        }
        if ($qsurveys->getState() == QSurveys::$STATE_INVITED || $qsurveys->getState() == QSurveys::$STATE_ONGOING) {
            $qsurveys->setState(QSurveys::$STATE_ONGOING);
            $qsurveys->setQno($page);
            $qsurveys->setDate(time());
            $em->persist($qsurveys);
            $em->flush();
        }
        else {
            return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig', array('survey' => $qsurveys));
        }
        
        return $this->render('TrainingCompanyQueryBundle:Default:suspend.html.twig');
    }
}
