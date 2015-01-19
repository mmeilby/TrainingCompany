<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Swift_Message;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Entity\Configuration;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\FormData;

class FeedbackController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/feedback/{id}", name="_feedback_with_id")
     * @Template("TrainingCompanyQueryBundle:Default:feedback.html.twig")
     */
    public function feedbackWithIDAction(Request $request, $id) {
        try {
            $em = $this->getDoctrine()->getManager();
            /* @var $qsurveys QSurveys */
            $qsurveys = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('token' => $id));
            if (!$qsurveys) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
            $qpersons = $em->getRepository(Configuration::PersonRepo())->find($qsurveys->getPid());
            if (!$qpersons) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
            $qschema = $em->getRepository(Configuration::SchemaRepo())->find($qsurveys->getSid());
            if (!$qschema) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
        }
        catch (\PDOException $e) {
            $session = $request->getSession();
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }

        $form = $this->makeForm($qpersons);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->sendMail($form, $request, $qpersons, $qschema);
            return $this->render('TrainingCompanyQueryBundle:Default:feedback_ty.html.twig');
        }

        return array(
            'form' => $form->createView(),
            'name' => $qpersons->getName(),
            'company' => $qschema->getName(),
            'signer' => $qschema->getSigner()
        );
    }
    
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/feedback", name="_feedback")
     * @Template("TrainingCompanyQueryBundle:Default:feedback.html.twig")
     */
    public function feedbackAction(Request $request) {
        $form = $this->makeForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->sendMail($form, $request);
            return $this->render('TrainingCompanyQueryBundle:Default:feedback_ty.html.twig');
        }

        return array('form' => $form->createView());
    }
    
    /**
     * Dan formular med elementer for den valgte side
     * @param $user user record
     * @param $pages antal spørgsmål
     * @return mixed den genererede formular
     */
    private function makeForm($user = null) {
        $formDef = $this->createFormBuilder();
        if (!isset($user)) {
            $formDef->add('name', 'text', array('label' => 'Navn', 'required' => false));
            $formDef->add('email', 'text', array('label' => 'E-mail', 'required' => false));
        }
        $formDef->add('feedback', 'textarea', array('label' => 'Kommentar', 'required' => false));
        $formDef->add('send', 'submit', array('label' => 'Send kommentar',
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-envelope-o'));
        return $formDef->getForm();
    }
    
    private function sendMail($form, $request, $user = null, $qschema = null) {
        $formData = $form->getData();
        $feedback = $formData['feedback'];
        $from = '';
        $sender = '';
        if (!isset($user)) {
            $from = $formData['email'];
            $sender = $formData['name'];
        }
        else {
            $from = $user->getEmail();
            $sender = $user->getName();
        }
        $parms = array(
            'name' => $sender,
            'email' => $from,
            'feedback' => $feedback,
            'host' => $request->getHost(),
            'survey' => isset($qschema) ? $qschema->getName() : '');
        $message = Swift_Message::newInstance()
            ->setSubject('TTC-TEST Feedback')
            ->setFrom(array($this->container->getParameter('mailuser') => $this->container->getParameter('admin-name')))
            ->setTo(array($this->container->getParameter('feedback-mail') => $this->container->getParameter('feedback-name')))
            ->setBody($this->renderView('TrainingCompanyQueryBundle:Default:feedbackmail.html.twig', $parms), 'text/html');
        $this->get('mailer')->send($message);
    }
}
