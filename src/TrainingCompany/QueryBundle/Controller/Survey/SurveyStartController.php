<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use TrainingCompany\QueryBundle\Controller\Survey\SurveyController;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Configuration;

class SurveyStartController extends Controller
{
    /**
     * Startside i brugerundersøgelse
     * Forudsætning: spørgeskema skal være defineret i session
     * @Route("/survey/start", name="_start")
     * @Template("TrainingCompanyQueryBundle:Default:start.html.twig")
     */
    public function startAction(Request $request) {
        $session = $request->getSession();
        // Reset channel size to large device
        $session->set(SurveyController::$sessionMobileDevice, false);
        $qid = $session->get(SurveyController::$sessionSurveyId);
        if (!isset($qid)) {
            return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            /* @var $user QPersons */
            $user = $this->get('security.context')->getToken()->getUser();
            if ($user == null) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
        
            $qsurveys = $em->getRepository(Configuration::SurveyRepo())->find($qid);
            if (!$qsurveys) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
            $qschema = $em->getRepository(Configuration::SchemaRepo())->find($qsurveys->getSid());
            if (!$qschema) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }

            if ($qsurveys->getState() == QSurveys::$STATE_INVITED) {
                $qsurveys->setState(QSurveys::$STATE_ONGOING);
                $qsurveys->setDate(time());
                $em->flush();
            }
            else if ($qsurveys->getState() == QSurveys::$STATE_FINISHED || $qsurveys->getState() == QSurveys::$STATE_OPTED) {
                return $this->render(
                        'TrainingCompanyQueryBundle:Default:finished_survey.html.twig',
                        array(
                            'survey' => $qsurveys,
                            'company' => $qschema->getName()));
            }

            $formDef = $this->createFormBuilder();
            $form = $formDef->getForm();
            return array('form' => $form->createView(),
                         'name' => $user->getName(),
                         'company' => $qschema->getName(),
                         'signer' => $qschema->getSigner());
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
    }
    
    /**
     * Nulstil aktuel side i brugerundersøgelse
     * Forudsætning: spørgeskema skal være defineret i session
     * @Route("/survey/restart", name="_restart")
     * @Template("TrainingCompanyQueryBundle:Default:start.html.twig")
     */
    public function restartAction(Request $request) {
        $session = $request->getSession();
        $session->set(SurveyController::$sessionPage, 0);
        return $this->redirect($this->generateUrl('_start'));
    }
}
