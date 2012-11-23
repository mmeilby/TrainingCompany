<?php
namespace TrainingCompany\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;

use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\FormData;

class DefaultController extends Controller
{
    private $repositoryPath = 'TrainingCompany\QueryBundle\Entity\Doctrine\QPersons';

    /**
     * Startpunkt for adgang til brugerundersøgelse via link
     * @Route("/survey/auth", name="_referred")
     * @Method("GET")
     */
    public function startAction() {
        $request = $this->getRequest();
        $q = $request->query->get('e');
        $id = $request->query->get('id');
        $session = $request->getSession();
        if (isset($q) && isset($id)) {
            try {
                $em = $this->getDoctrine()->getManager();
                $qpersons = $em->getRepository($this->repositoryPath)->findOneBy(array('token' => $id));
                if (!$qpersons) {
                    $session->set('error', 'Der er opstået en teknisk fejl. Start igen ved at klikke på det link der er tilsendt per e-mail. Vi undskylder ulejligheden!');
                    return $this->redirect($this->generateUrl('_error'));
                }

                $session->set('personid', $qpersons->getId());
                $queryBuilder = new QueryBuilderFactory();
                $session->set('questionid', $queryBuilder->getQueryId($q));
                return $this->redirect($this->generateUrl('_start'));
            }
            catch (\PDOException $e) {
                $session->set('error', $e->getMessage());
                return $this->redirect($this->generateUrl('_error'));
            }
        }

        $session->set('error', 'Start undersøgelsen ved at klikke på det link der er tilsendt per e-mail.');
        return $this->redirect($this->generateUrl('_error'));
    }

    /**
     * Startside i brugerundersøgelse
     * Forudsætning: brugerid og spørgeskema skal være defineret i session
     * @Route("/survey/start", name="_start")
     * @Template("TrainingCompanyQueryBundle:Default:start.html.twig")
     */
    public function automatedStartAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $pid = $session->get('personid');
        $qid = $session->get('questionid');
        if (!isset($pid) || !isset($qid)) {
            $session->set('error', 'Undersøgelsen er udløbet. Start igen ved at klikke på det link der er tilsendt per e-mail. Vi undskylder ulejligheden!');
            return $this->redirect($this->generateUrl('_error'));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $qpersons = $em->getRepository($this->repositoryPath)->find($pid);
            if (!$qpersons) {
                $session->set('error', 'Der er opstået en teknisk fejl. Start igen ved at klikke på det link der er tilsendt per e-mail. Vi undskylder ulejligheden!');
                return $this->redirect($this->generateUrl('_error'));
            }

            $page = 0;
            $session->set('page', $page);

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
     * Actionhandler til håndtering af tilbage knap i brugerundersøgelse
     * @Route("/survey/back", name="_back")
     * @Template("TrainingCompanyQueryBundle:Default:index.html.twig")
     */
    public function queryBackAction() {
        $request = $this->getRequest();
    	$session = $request->getSession();
        $pid = $session->get('personid');
        $qid = $session->get('questionid');
        $page = $session->get('page');
        if (!isset($pid) || !isset($qid) || !isset($page)) {
            $session->set('error', 'Undersøgelsen er udløbet. Start igen ved at klikke på det link der er tilsendt per e-mail. Vi undskylder ulejligheden!');
            return $this->redirect($this->generateUrl('_error'));
        }

        try {
            $page = $page - 1;
            if ($page < 0) $page = 0;
            $session->set('page', $page);

            $queryBuilder = new QueryBuilderFactory();
            $db = $queryBuilder->loadDatabase();
            $pages = count($db);

            $queryBlock = $db[$page];
            $form = $this->makeForm($queryBlock, $pid, $qid, $page);

            return array('form' => $form->createView(), 'page' => $page + 1, 'pages' => $pages);
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
    }
    
    /**
     * @Route("/survey", name="_survey")
     * @Template("TrainingCompanyQueryBundle:Default:index.html.twig")
     */
    public function queryAction(Request $request) {
        $session = $request->getSession();
        $pid = $session->get('personid');
        $qid = $session->get('questionid');
       	$page = $session->get('page');
        if (!isset($pid) || !isset($qid) || !isset($page)) {
            // Din session blev afbrudt. Klik på dit link igen for at fortsætte hvor du slap. Vi undskylder ulejligheden!
            $session->set('error', 'Undersøgelsen er udløbet. Start igen ved at klikke på det link der er tilsendt per e-mail. Vi undskylder ulejligheden!');
            return $this->redirect($this->generateUrl('_error'));
        }

        try {
            $queryBuilder = new QueryBuilderFactory();
            $db = $queryBuilder->loadDatabase();
            $pages = count($db);

            if ($request->getMethod() == 'POST') {
                $queryBlock = $db[$page];
                $form = $this->makeForm($queryBlock, $pid, $qid, $page);
                $form->bind($request);
                if ($form->isValid()) {
                    $formData = $form->getData();
                    $em = $this->getDoctrine()->getManager();
                    foreach ($queryBlock as $block) {
                        $block->readForm($formData);
                        $block->persist($em, $pid, $qid, $page);
                    }
                    $em->flush();
                    $page = $page + 1;
                    if ($page >= $pages) $page = $pages-1;
                    $session->set('page', $page);
                }
            }

            $queryBlock = $db[$page];
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
     * @Template("TrainingCompanyQueryBundle:Default:error.html.twig")
     */
    public function errorAction(Request $request) {
        $formDef = $this->createFormBuilder();
        $form = $formDef->getForm();
        $session = $request->getSession();
        $error = $session->get('error');
        return array('form' => $form->createView(), 'error' => $error);
    }

    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/test", name="_test")
     * @Method("GET")
     */
    public function testAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $session->set('personid', 666);
        $session->set('questionid', 99);
        return $this->redirect($this->generateUrl('_start'));
    }

    /**
     * Dan formular med elementer for den valgte side
     * @param $queryBlock beskrivelse af sidens elementer
     * @param $pid bruger id
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
