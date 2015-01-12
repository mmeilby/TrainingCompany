<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Controller\Survey\SurveyController;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;

class SurveyStartController extends Controller
{
    /**
     * Startpunkt for adgang til brugerundersøgelse via link
     * @Route("/survey/auth", name="_referred")
     * @Method("GET")
     */
    public function startAction(Request $request) {
        $id = $request->query->get('id');
        $session = $request->getSession();
        if (!isset($id)) {
            return $this->render('TrainingCompanyQueryBundle:Default:invalid_params.html.twig');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $qsurveys = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('token' => $id));
            if (!$qsurveys) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }

            if ($qsurveys->getState() == QSurveys::$STATE_INVITED) {
                $session->set(SurveyController::$sessionPage, 0);
            }
            else if ($qsurveys->getState() == QSurveys::$STATE_ONGOING) {
                $session->set(SurveyController::$sessionPage, $qsurveys->getQno());
            }
            else {
                return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig', array('survey' => $qsurveys));
            }

            $session->set(SurveyController::$sessionPersonId, $qsurveys->getPid());
            $session->set(SurveyController::$sessionSurveyId, $qsurveys->getId());
            $session->set(SurveyController::$sessionTemplateId, $qsurveys->getSid());
            return $this->redirect($this->generateUrl('_start'));
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
    }

    /**
     * Startside i brugerundersøgelse
     * Forudsætning: spørgeskema skal være defineret i session
     * @Route("/survey/start", name="_start")
     * @Template("TrainingCompanyQueryBundle:Default:start.html.twig")
     */
    public function automatedStartAction(Request $request) {
        $session = $request->getSession();
        $pid = $session->get(SurveyController::$sessionPersonId);
        $qid = $session->get(SurveyController::$sessionSurveyId);
        if (!isset($pid) || !isset($qid)) {
            return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $qpersons = $em->getRepository(Configuration::PersonRepo())->find($pid);
            if (!$qpersons) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }

            $qsurveys = $em->getRepository(Configuration::SurveyRepo())->find($qid);
            if (!$qsurveys) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
            if ($qsurveys->getState() == QSurveys::$STATE_INVITED) {
                $qsurveys->setState(QSurveys::$STATE_ONGOING);
                $qsurveys->setDate(time());
                $em->persist($qsurveys);
                $em->flush();
            }
            else if ($qsurveys->getState() == QSurveys::$STATE_FINISHED || $qsurveys->getState() == QSurveys::$STATE_OPTED) {
                return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig', array('survey' => $qsurveys));
            }

            $formDef = $this->createFormBuilder();
            $form = $formDef->getForm();
            return array('form' => $form->createView(), 'name' => $qpersons->getName());
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
    }
}
