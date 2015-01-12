<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Controller\Survey\SurveyController;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;

class OptoutController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/opt", name="_optout")
     * @Template("TrainingCompanyQueryBundle:Default:optout.html.twig")
     */
    public function optAction(Request $request) {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()->getForm();
        if ($request->getMethod() != 'POST') {
            $id = $request->query->get('id');
            if (!isset($id)) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_params.html.twig');
            }

            try {
                $qsurveys = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('token' => $id));
                if (!$qsurveys) {
                    return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
                }

                $session->set(SurveyController::$sessionPersonId, $qsurveys->getPid());
                $session->set(SurveyController::$sessionSurveyId, $qsurveys->getId());
                $session->set(SurveyController::$sessionTemplateId, $qsurveys->getSid());
                
                if ($qsurveys->getState() != QSurveys::$STATE_INVITED && $qsurveys->getState() != QSurveys::$STATE_ONGOING) {
                    return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig', array('survey' => $qsurveys));
                }
                
                return array('form' => $form->createView());
            }
            catch (\PDOException $e) {
                $session->set('error', $e->getMessage());
                return $this->redirect($this->generateUrl('_error'));
            }
        }
        else {
            $form->bind($request);
            if ($form->isValid()) {

                try {
                    $qid = $session->get(SurveyController::$sessionSurveyId);
                    $qsurveys = $em->getRepository(Configuration::SurveyRepo())->find($qid);
                    if (!$qsurveys) {
                        return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
                    }
                    if ($qsurveys->getState() == QSurveys::$STATE_INVITED || $qsurveys->getState() == QSurveys::$STATE_ONGOING) {
                        $qsurveys->setState(QSurveys::$STATE_OPTED);
                        $qsurveys->setDate(time());
                        $em->persist($qsurveys);
                        $em->flush();
                    }
                    return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig', array('survey' => $qsurveys));
                }
                catch (\PDOException $e) {
                    $session->set('error', $e->getMessage());
                    return $this->redirect($this->generateUrl('_error'));
                }
            }
        }

        return $this->render('TrainingCompanyQueryBundle:Default:optout.html.twig');
    }
}
