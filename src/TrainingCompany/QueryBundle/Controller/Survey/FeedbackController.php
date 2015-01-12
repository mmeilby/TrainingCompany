<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Swift_Message;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Controller\Survey\SurveyController;
use TrainingCompany\QueryBundle\Entity\Configuration;

class FeedbackController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/survey/feedback", name="_feedback")
     * @Template("TrainingCompanyQueryBundle:Default:feedback.html.twig")
     */
    public function feedbackAction(Request $request) {
        $session = $request->getSession();
        if ($request->getMethod() != 'POST') {
            $id = $request->query->get('id');
            if (isset($id)) {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $qsurveys = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('token' => $id));
                    if (!$qsurveys) {
                        return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
                    }

                    $session->set(SurveyController::$sessionPersonId, $qsurveys->getPid());
                    $session->set(SurveyController::$sessionSurveyId, $qsurveys->getId());
                    $session->set(SurveyController::$sessionTemplateId, $qsurveys->getSid());
                }
                catch (\PDOException $e) {
                    $session->set('error', $e->getMessage());
                    return $this->redirect($this->generateUrl('_error'));
                }
            }
        }
        $pid = $session->get(SurveyController::$sessionPersonId);

        $formDef = $this->createFormBuilder();
        if (!isset($pid)) {
            $formDef->add('name', 'text', array('label' => 'Navn', 'required' => false));
            $formDef->add('email', 'text', array('label' => 'E-mail', 'required' => false));
        }
        $formDef->add('feedback', 'textarea', array('label' => 'Kommentar', 'required' => false));
        $formDef->add('send', 'submit', array('label' => 'Send kommentar',
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-envelope-o'));
        $form = $formDef->getForm();
        if ($request->getMethod() != 'POST') {
           return array('form' => $form->createView());
        }
        else {
            $form->bind($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $feedback = $formData['feedback'];
                $from = '';
                $sender = '';
                if (!isset($pid)) {
                    $from = $formData['email'];
                    $sender = $formData['name'];
                }
                else {
                    try {
                       $em = $this->getDoctrine()->getManager();
                       $qpersons = $em->getRepository(Configuration::PersonRepo())->find($pid);
                       if ($qpersons) {
                           $from = $qpersons->getEmail();
                           $sender = $qpersons->getName();
                       }
                   }
                   catch (\PDOException $e) {
                       $session->set('error', $e->getMessage());
                       return $this->redirect($this->generateUrl('_error'));
                   }
                }
                $parms = array(
                    'name' => $sender,
                    'email' => $from,
                    'feedback' => $feedback,
                    'host' => $request->getHost());
                $message = Swift_Message::newInstance()
                    ->setSubject('TTC-TEST Feedback')
                    ->setFrom(array($this->container->getParameter('mailuser') => $this->container->getParameter('admin-name')))
                    ->setTo(array($this->container->getParameter('feedback-mail') => $this->container->getParameter('feedback-name')))
                    ->setBody($this->renderView('TrainingCompanyQueryBundle:Default:feedbackmail.html.twig', $parms), 'text/html');
                $this->get('mailer')->send($message);
                
            }
            return $this->render('TrainingCompanyQueryBundle:Default:feedback_ty.html.twig');
        }
    }
}
