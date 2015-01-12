<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use JMS\SecurityExtraBundle\Annotation\Secure;
use TrainingCompany\QueryBundle\Entity\Admin\NewPerson;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;
use TrainingCompany\QueryBundle\Controller\QueryBuilderFactory;

class AdminController extends Controller
{
    /**
     * Administrationsside
     * @Route("/admin", name="_admin")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Default:admin.html.twig")
     */
    public function startAction() {
        $formData = new NewPerson();
        $formDef = $this->createFormBuilder($formData);
        $formDef->add('name', 'text', array('label' => 'Navn', 'required' => false));
        $formDef->add('email', 'text', array('label' => 'E-mail', 'required' => false));

        $em = $this->getDoctrine()->getManager();

        $queryBuilder = new QueryBuilderFactory();
        $surveys = $queryBuilder->getSurveys($em);
        $choices = array();
        foreach ($surveys as $survey) {
            $choices[$survey->id] = $survey->name;
        }
        $formDef->add('survey', 'choice', array('label' => 'Spørgeskema', 'required' => false, 'choices' => $choices, 'empty_value' => 'Vælg...'));
        $formDef->add('invite', 'submit', array('label' => 'Inviter',
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-envelope-o'));
        $form = $formDef->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $qpersons = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('email' => $formData->email));
                $new = !$qpersons;
                if ($new) $qpersons = new QPersons();
                $qpersons->setName($formData->name);
                $qpersons->setEmail($formData->email);
                if ($new) $em->persist($qpersons);
                $em->flush();

                $qsurvey = new QSurveys();
                $qsurvey->setPid($qpersons->getId());
                $qsurvey->setSid($formData->survey);
                $qsurvey->setQno(0);
                $qsurvey->setDate(time());
                $qsurvey->setState(0);
                $qsurvey->setToken($this->generateToken($formData->email));
                $em->persist($qsurvey);
                $em->flush();
                
                foreach ($surveys as $survey) {
                    if ($survey->id == $formData->survey) {
                        $parms = array(
                            'from' => $survey->email,
                            'sender' => $survey->signer,
                            'name' => $qpersons->getName(),
                            'email' => $qpersons->getEmail(),
                            'token' => $qsurvey->getToken(),
//                            'survey' => $survey->ref,
                            'host' => $request->getHost());
                        $message = Swift_Message::newInstance()
                            ->setSubject($survey->invitation)
                            ->setFrom(array($survey->email => $survey->sender))
                            ->setTo(array($qpersons->getEmail() => $qpersons->getName()))
                            ->setBody($this->renderView('TrainingCompanyQueryBundle:Default:invitemail.html.twig', $parms), 'text/html');
        //                    ->addPart($this->renderView('TrainingCompanyQueryBundle:Default:invitemail.html.twig', $parms));
                        $this->get('mailer')->send($message);
                        return array('form' => $form->createView(),
                            'send_name' => $qpersons->getName(),
                            'send_email' => $qpersons->getEmail(),
                            'send_token' => $qsurvey->getToken());
                   }
                }
            }
        }

        return array('form' => $form->createView());
    }

    private function generateToken($name) {
        $str = dechex(time()).'-'.
               dechex(rand(4096, 65535)).'-'.
               dechex(rand(4096, 65535)).'-'.
               dechex(rand(4096, 65535)).'-'.
               substr(bin2hex(str_shuffle(str_pad($name, 6))), 0, 12);
        return $str;
    }
    
    /**
     * Fejlside til præsentation af tekniske fejl
     * @Route("/admin/error", name="_admin_error")
     * @Template("TrainingCompanyQueryBundle:Default:error.html.twig")
     */
    public function errorAction(Request $request) {
        $formDef = $this->createFormBuilder();
        $form = $formDef->getForm();
        $session = $request->getSession();
        $error = $session->get('error');
        return array('form' => $form->createView(), 'error' => $error);
    }
}
