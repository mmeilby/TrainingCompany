<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\FormData;
use TrainingCompany\QueryBundle\Controller\QueryBuilderFactory;
use TrainingCompany\QueryBundle\Entity\Configuration;

class SurveyController extends Controller
{
    public static $sessionSurveyId = 'surveyid';
    public static $sessionTemplateId = 'templateId';
    public static $sessionPage = 'page';
    public static $sessionMobileDevice = 'mobileDevice';

    /**
     * Entry point for query page for survey on mobile devices
     * @Route("/survey/xs", name="_survey_xs")
     * @Template("TrainingCompanyQueryBundle:Default:survey.html.twig")
     */
    public function queryActionXs(Request $request) {
        $session = $request->getSession();
        $session->set(SurveyController::$sessionMobileDevice, true);
        return $this->redirect($this->generateUrl('_survey'));
    }
    
    /**
     * Query page for the survey
     * @Route("/survey", name="_survey")
     * @Template("TrainingCompanyQueryBundle:Default:survey.html.twig")
     */
    public function queryAction(Request $request) {
        $session = $request->getSession();
        $qid = $session->get(SurveyController::$sessionSurveyId);
        $tid = $session->get(SurveyController::$sessionTemplateId);
      	$page = $session->get(SurveyController::$sessionPage);
        if (!isset($qid) || !isset($page) || !isset($tid)) {
            return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        /* @var $user QPersons */
        $user = $this->get('security.context')->getToken()->getUser();
        if ($user == null) {
            return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = new QueryBuilderFactory();
        $survey = $queryBuilder->loadTemplate($em, $tid, $session->get(SurveyController::$sessionMobileDevice, false));
        $template = $survey->queryblocks;
        $pages = count($template);

        $qschema = $em->getRepository(Configuration::SchemaRepo())->find($tid);
        if (!$qschema) {
            return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
        }

        $queryBlock = $template[$page];
        $form = $this->makeForm($queryBlock, $user->getId(), $qid, $page, $pages);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $formData = $form->getData();
            foreach ($queryBlock as $block) {
                $block->readForm($formData);
                $block->persist($em, $user->getId(), $qid, $page);
            }
            $em->flush();
            if ($formData->direction == "suspend") {
                return $this->redirect($this->generateUrl('_suspend'));
            } else {
                $page = $formData->direction-1;
                if ($page >= $pages) {
                    $qsurveys = $em->getRepository(Configuration::SurveyRepo())->find($qid);
                    if (!$qsurveys) {
                        return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
                    }
                    $qsurveys->setState(QSurveys::$STATE_FINISHED);
                    $qsurveys->setDate(time());
                    $em->flush();
                    return $this->render(
                            'TrainingCompanyQueryBundle:Default:end.html.twig',
                            array(
                                'survey' => $qsurveys,
                                'company' => $qschema->getName()));
                }
            }
            $session->set(SurveyController::$sessionPage, $page);
            $queryBlock = $template[$page];
            $form = $this->makeForm($queryBlock, $user->getId(), $qid, $page, $pages);
        }
        return array('form' => $form->createView(),
                     'company' => $qschema->getName(),
                     'page' => $page + 1,
                     'pages' => $pages);
    }

    /**
     * Dan formular med elementer for den valgte side
     * @param $queryBlock beskrivelse af sidens elementer
     * @param $pid person id
     * @param $qid spørgeskema id
     * @param $qno spørgsmål nr.
     * @param $pages antal spørgsmål
     * @return mixed den genererede formular
     */
    private function makeForm($queryBlock, $pid, $qid, $qno, $pages) {
        $formData = new FormData();
        $formDef = $this->createFormBuilder($formData);
        $em = $this->getDoctrine()->getManager();
        foreach ($queryBlock as $block) {
            $block->get($em, $pid, $qid, $qno);
            $block->populateForm($formData, $formDef);
        }
        $formDef->add('direction', 'hidden');
        return $formDef->getForm();
    }
}
