<?php
namespace TrainingCompany\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;

use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\FormData;

class DefaultController extends Controller
{
    private $repositoryPathSurveys = 'TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys';
    private $repositoryPathPersons = 'TrainingCompany\QueryBundle\Entity\Doctrine\QPersons';

    private $sessionPersonId = 'personid';
    private $sessionSurveyId = 'surveyid';
    private $sessionTemplateId = 'templateId';
    private $sessionPage = 'page';
    
    /**
     * Startpunkt for adgang til brugerundersøgelse via link
     * @Route("/survey/auth", name="_referred")
     * @Method("GET")
     */
    public function startAction() {
        $request = $this->getRequest();
        $id = $request->query->get('id');
        $session = $request->getSession();
        if (isset($id)) {
            try {
                $em = $this->getDoctrine()->getManager();
                $qsurveys = $em->getRepository($this->repositoryPathSurveys)->findOneBy(array('token' => $id));
                if (!$qsurveys) {
                    return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
                }

                if ($qsurveys->getState() == QSurveys::$STATE_INVITED) {
                    $session->set($this->sessionPage, 0);
                }
                else if ($qsurveys->getState() == QSurveys::$STATE_ONGOING) {
                    $session->set($this->sessionPage, $qsurveys->getQno());
                }
                else {
                    return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig', array('survey' => $qsurveys));
                }
                
                $session->set($this->sessionPersonId, $qsurveys->getPid());
                $session->set($this->sessionSurveyId, $qsurveys->getId());
                $session->set($this->sessionTemplateId, $qsurveys->getSid());
                return $this->redirect($this->generateUrl('_start'));
            }
            catch (\PDOException $e) {
                $session->set('error', $e->getMessage());
                return $this->redirect($this->generateUrl('_error'));
            }
        }

        return $this->render('TrainingCompanyQueryBundle:Default:invalid_params.html.twig');
    }

    /**
     * Startside i brugerundersøgelse
     * Forudsætning: spørgeskema skal være defineret i session
     * @Route("/survey/start", name="_start")
     * @Template("TrainingCompanyQueryBundle:Default:start.html.twig")
     */
    public function automatedStartAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $pid = $session->get($this->sessionPersonId);
        $qid = $session->get($this->sessionSurveyId);
        if (!isset($pid) || !isset($qid)) {
            return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $qpersons = $em->getRepository($this->repositoryPathPersons)->find($pid);
            if (!$qpersons) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }

            $qsurveys = $em->getRepository($this->repositoryPathSurveys)->find($qid);
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

    /**
     * @Route("/survey", name="_survey")
     * @Template("TrainingCompanyQueryBundle:Default:survey.html.twig")
     */
    public function queryAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $pid = $session->get($this->sessionPersonId);
        $qid = $session->get($this->sessionSurveyId);
        $tid = $session->get($this->sessionTemplateId);
      	$page = $session->get($this->sessionPage);
        if (!isset($pid) || !isset($qid) || !isset($page) || !isset($tid)) {
            return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $queryBuilder = new QueryBuilderFactory();
            $survey = $queryBuilder->loadTemplate($em, $tid);
            $template = $survey->queryblocks;
            $pages = count($template);

            if ($request->getMethod() == 'POST') {
                $queryBlock = $template[$page];
                $form = $this->makeForm($queryBlock, $pid, $qid, $page);
                $form->bind($request);
                if ($form->isValid()) {
                    $formData = $form->getData();
                    foreach ($queryBlock as $block) {
                        $block->readForm($formData);
                        $block->persist($em, $pid, $qid, $page);
                    }
                    $em->flush();
                    $page = $page + 1;
                    if ($page >= $pages) {
                        $qsurveys = $em->getRepository($this->repositoryPathSurveys)->find($qid);
                        if (!$qsurveys) {
                            return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
                        }
                        $qsurveys->setState(QSurveys::$STATE_FINISHED);
                        $qsurveys->setDate(time());
                        $em->persist($qsurveys);
                        $em->flush();
                        return $this->render('TrainingCompanyQueryBundle:Default:end.html.twig', array('survey' => $qsurveys));
                    }
                    $session->set($this->sessionPage, $page);
                }
            }

            $queryBlock = $template[$page];
            $form = $this->makeForm($queryBlock, $pid, $qid, $page);
            return array('form' => $form->createView(), 'page' => $page + 1, 'pages' => $pages);
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
    }

    /**
     * Actionhandler til håndtering af tilbage knap i brugerundersøgelse
     * @Route("/survey/back", name="_back")
     * @Template("TrainingCompanyQueryBundle:Default:survey.html.twig")
     */
    public function queryBackAction() {
        $request = $this->getRequest();
    	$session = $request->getSession();
        $pid = $session->get($this->sessionPersonId);
        $qid = $session->get($this->sessionSurveyId);
        $tid = $session->get($this->sessionTemplateId);
        $page = $session->get($this->sessionPage);
        if (!isset($pid) || !isset($qid) || !isset($page) || !isset($tid)) {
            return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        try {
            $page = $page - 1;
            if ($page < 0) $page = 0;
            $session->set($this->sessionPage, $page);

            $em = $this->getDoctrine()->getManager();
            $queryBuilder = new QueryBuilderFactory();
            $survey = $queryBuilder->loadTemplate($em, $tid);
            $template = $survey->queryblocks;
            $pages = count($template);

            $queryBlock = $template[$page];
            $form = $this->makeForm($queryBlock, $pid, $qid, $page);

            return array('form' => $form->createView(), 'page' => $page + 1, 'pages' => $pages);
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
    }
    
    /**
     * Fejlside til præsentation af tekniske fejl
     * @Route("/survey/error", name="_error")
     */
    public function errorAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $error = $session->get('error');
        return $this->render('TrainingCompanyQueryBundle:Default:error.html.twig', array('error' => $error));
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/feedback", name="_feedback")
     * @Method("GET")
     */
    public function feedbackAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $pid = $session->get($this->sessionPersonId);
        $tid = $session->get($this->sessionSurveyId);
       	$page = $session->get($this->sessionPage);
        if (!isset($pid) || !isset($tid) || !isset($page)) {
             return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }
        return $this->redirect($this->generateUrl('_start'));
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/suspend", name="_suspend")
     * @Method("GET")
     */
    public function suspendAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $qid = $session->get($this->sessionSurveyId);
       	$page = $session->get($this->sessionPage);
        if (!isset($qid) || !isset($page)) {
             return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        $em = $this->getDoctrine()->getManager();
        $qsurveys = $em->getRepository($this->repositoryPathSurveys)->find($qid);
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

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/opt", name="_optout")
     * @Method("GET")
     */
    public function optAction() {
        $request = $this->getRequest();
        $id = $request->query->get('id');
        $session = $request->getSession();
        if (isset($id)) {
            try {
                $em = $this->getDoctrine()->getManager();
                $qsurveys = $em->getRepository($this->repositoryPathSurveys)->findOneBy(array('token' => $id));
                if (!$qsurveys) {
                    return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
                }

                if ($qsurveys->getState() == QSurveys::$STATE_INVITED || $qsurveys->getState() == QSurveys::$STATE_ONGOING) {
                    $qsurveys->setState(QSurveys::$STATE_OPTED);
                    $qsurveys->setDate(time());
                    $em->persist($qsurveys);
                    $em->flush();
                }
                else {
                    return $this->render('TrainingCompanyQueryBundle:Default:finished_survey.html.twig', array('survey' => $qsurveys));
                }
                
//                $session->set($this->sessionPersonId, $qsurveys->getPid());
//                $session->set($this->sessionSurveyId, $qsurveys->getId());
//                $session->set($this->sessionTemplateId, $qsurveys->getSid());
//                return $this->redirect($this->generateUrl('_start'));
            }
            catch (\PDOException $e) {
                $session->set('error', $e->getMessage());
                return $this->redirect($this->generateUrl('_error'));
            }
        }

        return $this->render('TrainingCompanyQueryBundle:Default:optout.html.twig');
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/load", name="_load")
     * @Method("GET")
     */
    public function loadAction() {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = new QueryBuilderFactory();
        $template = $queryBuilder->getTemplate(1);
        $survey = new \TrainingCompany\QueryBundle\Entity\Survey();
        $survey->name = 'yousee';
        $survey->signer = 'Henrik Vrangbæk Thomsen';
        $survey->queryblocks = $template;
        $queryBuilder->saveTemplate($em, $survey);
        return $this->render('TrainingCompanyQueryBundle:Default:optout.html.twig');
    }

    /**
     * Dan formular med elementer for den valgte side
     * @param $queryBlock beskrivelse af sidens elementer
     * @param $pid person id
     * @param $qid spørgeskema id
     * @param $qno spørgsmål nr.
     * @return mixed den genererede formular
     */
    private function makeForm($queryBlock, $pid, $qid, $qno) {
        $formData = new FormData();
        $formDef = $this->createFormBuilder($formData);
        $em = $this->getDoctrine()->getManager();
        foreach ($queryBlock as $block) {
            $block->get($em, $pid, $qid, $qno);
            $block->populateForm($formData, $formDef);
        }
        return $formDef->getForm();
    }
}
