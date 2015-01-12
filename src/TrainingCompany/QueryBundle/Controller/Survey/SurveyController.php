<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\FormData;
use TrainingCompany\QueryBundle\Controller\QueryBuilderFactory;
use TrainingCompany\QueryBundle\Entity\Configuration;

class SurveyController extends Controller
{
    public static $sessionPersonId = 'personid';
    public static $sessionSurveyId = 'surveyid';
    public static $sessionTemplateId = 'templateId';
    public static $sessionPage = 'page';
    
    /**
     * @Route("/survey", name="_survey")
     * @Template("TrainingCompanyQueryBundle:Default:survey.html.twig")
     */
    public function queryAction(Request $request) {
        $session = $request->getSession();
        $pid = $session->get(SurveyController::$sessionPersonId);
        $qid = $session->get(SurveyController::$sessionSurveyId);
        $tid = $session->get(SurveyController::$sessionTemplateId);
      	$page = $session->get(SurveyController::$sessionPage);
        if (!isset($pid) || !isset($qid) || !isset($page) || !isset($tid)) {
            return $this->render('TrainingCompanyQueryBundle:Default:timeout.html.twig');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $queryBuilder = new QueryBuilderFactory();
            $survey = $queryBuilder->loadTemplate($em, $tid);
            $template = $survey->queryblocks;
            $pages = count($template);

            $queryBlock = $template[$page];
            $form = $this->makeForm($queryBlock, $pid, $qid, $page, $pages);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                foreach ($queryBlock as $block) {
                    $block->readForm($formData);
                    $block->persist($em, $pid, $qid, $page);
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
                        $em->persist($qsurveys);
                        $em->flush();
                        return $this->render('TrainingCompanyQueryBundle:Default:end.html.twig', array('survey' => $qsurveys));
                    }
                }
                $session->set(SurveyController::$sessionPage, $page);
                $queryBlock = $template[$page];
                $form = $this->makeForm($queryBlock, $pid, $qid, $page, $pages);
            }
            return array('form' => $form->createView(), 'page' => $page + 1, 'pages' => $pages);
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
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
/*        
        if ($qno > 0) {
            $formDef->add('back', 'submit', array('label' => 'Tilbage',
                                                    'translation_domain' => 'admin',
                                                    'icon' => 'fa fa-times'));
        }
        $formDef->add('suspend', 'submit', array('label' => 'Pause',
                                                'translation_domain' => 'admin',
                                                'buttontype' => 'btn btn-default',
                                                'icon' => 'fa fa-times'));
        if ($qno+1 == $pages) {
            $formDef->add('complete', 'submit', array('label' => 'Afslut',
                                                    'translation_domain' => 'admin',
                                                    'icon' => 'fa fa-check'));
        }
        else {
            $formDef->add('next', 'submit', array('label' => 'Videre',
                                                    'translation_domain' => 'admin',
                                                    'icon' => 'fa fa-times'));
        }
 * 
 */
        return $formDef->getForm();
    }
}
