<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;
use TrainingCompany\QueryBundle\Entity\Admin\SurveyForm;

class SurveyController extends Controller
{
    /**
     * Add new survey
     * @Route("/admin/add/survey", name="_admin_survey_add")
     * @Template("TrainingCompanyQueryBundle:Admin:editsurvey.html.twig")
     */
    public function addAction(Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $survey = new QSurveys();
        $form = $this->makeForm($survey, 'add');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($this->checkForm($form, $survey)) {
            $em->persist($survey);
            $em->flush();
            return $this->redirect($returnUrl);
        }
        return array('form' => $form->createView(), 'action' => 'add', 'survey' => $survey);
    }
    
   /**
     * Change survey information
     * @Route("/admin/chg/survey/{id}", name="_admin_survey_chg")
     * @Template("TrainingCompanyQueryBundle:Admin:editsurvey.html.twig")
     */
    public function chgAction($id, Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $survey = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('id' => $id));

        $form = $this->makeForm($survey, 'chg');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($this->checkForm($form, $survey)) {
            $em->flush();
            return $this->redirect($returnUrl);
        }
        return array('form' => $form->createView(), 'action' => 'chg', 'survey' => $survey);
    }
    
   /**
     * Delete survey information
     * @Route("/admin/del/survey/{id}", name="_admin_survey_del")
     * @Template("TrainingCompanyQueryBundle:Admin:editsurvey.html.twig")
     */
    public function delAction($id, Request $request) {
        $returnUrl = $this->getReferer($request);
        $em = $this->getDoctrine()->getManager();

        $survey = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('id' => $id));

        $form = $this->makeForm($survey, 'del');
        $form->handleRequest($request);
        if ($form->get('cancel')->isClicked()) {
            return $this->redirect($returnUrl);
        }
        if ($form->isValid()) {
            $this->removeSurvey($survey);
            return $this->redirect($returnUrl);
        }
        return array('form' => $form->createView(), 'action' => 'del', 'survey' => $survey);
    }

    private function makeForm(QSurveys $survey, $action) {
        $status = array();
        foreach (array(QSurveys::$STATE_INVITED, QSurveys::$STATE_ONGOING, QSurveys::$STATE_FINISHED, QSurveys::$STATE_OPTED) as $stat) {
            $status[$stat] = 'FORM.SURVEY.CHOICE.STATUS.'.$stat;
        }
        $surveyForm = new SurveyForm();
        $surveyForm->date = $survey->getDate() != null ? date("j-M-Y", $survey->getDate()) : "";
        $surveyForm->state = $survey->getState();
        $formDef = $this->createFormBuilder($surveyForm);
        $formDef->add('state', 'choice', array('label' => 'FORM.SURVEY.STATE', 'required' => false, 'choices' => $status, 'placeholder' => 'FORM.SURVEY.DEFAULT', 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('date', 'text', array('label' => 'FORM.SURVEY.DATE.LABEL', 'help' => 'FORM.SURVEY.DATE.HELP', 'required' => false, 'disabled' => $action == 'del', 'translation_domain' => 'admin'));
        $formDef->add('cancel', 'submit', array('label' => 'FORM.SURVEY.CANCEL.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'buttontype' => 'btn btn-default',
                                                'icon' => 'fa fa-times'));
        $formDef->add('save', 'submit', array('label' => 'FORM.SURVEY.SUBMIT.'.strtoupper($action),
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-check'));
        return $formDef->getForm();
    }
    
    private function checkForm($form, QSurveys $survey) {
        $surveyForm = $form->getData();
        if ($form->isValid()) {
            $noErrors = true;
            if ($surveyForm->date == null || trim($surveyForm->date) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.SURVEY.NODATE', array(), 'admin')));
                $noErrors = false;
            }
            else {
                $survey->setDate(date_create_from_format("j-M-Y", $surveyForm->date));
            }
            if ($surveyForm->state === null) {
                $form->addError(new FormError($this->get('translator')->trans('FORM.SURVEY.NOSTATE', array(), 'admin')));
                $noErrors = false;
            }
            else {
                $survey->setState($surveyForm->state);
            }
            return $noErrors;
        }
        return false;
    }

     private function removeSurvey(QSurveys $survey) {
        $em = $this->getDoctrine()->getManager();
        $em->createQuery(
            "delete from ".Configuration::ResponseRepo()." r ".
            "where r.qid=:survey")
                ->setParameter('survey', $survey->getId())
                ->getResult();
        $em->createQuery(
            "delete from ".Configuration::CommentRepo()." c ".
            "where c.qid=:survey")
                ->setParameter('survey', $survey->getId())
                ->getResult();
        $em->remove($survey);
        $em->flush();
    }
    
   private function getReferer(Request $request) {
        if ($request->isMethod('GET')) {
            $returnUrl = $request->headers->get('referer');
            $session = $request->getSession();
            $session->set('icup.referer', $returnUrl);
        }
        else {
            $session = $request->getSession();
            $returnUrl = $session->get('icup.referer');
        }
        return $returnUrl;
    }
}
