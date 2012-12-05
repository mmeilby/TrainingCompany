<?php
namespace TrainingCompany\QueryBundle\Controller;

use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;
use TrainingCompany\QueryBundle\Entity\Admin\NewPerson;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;

class AdminController extends Controller
{
    private $repositoryPath = 'TrainingCompany\QueryBundle\Entity\Doctrine\QPersons';

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
        $form = $formDef->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $qpersons = $em->getRepository($this->repositoryPath)->findOneBy(array('email' => $formData->email));
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
                    }
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/admin/login", name="_admin_login")
     * @Template("TrainingCompanyQueryBundle:Default:login.html.twig")
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        );
    }

    /**
     * @Route("/admin/login_check", name="_security_check")
     */
    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/admin/logout", name="_admin_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
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

    private function generateToken($name) {
        $str = dechex(time()).'-'.
               dechex(rand(4096, 65535)).'-'.
               dechex(rand(4096, 65535)).'-'.
               dechex(rand(4096, 65535)).'-'.
               substr(bin2hex(str_shuffle(str_pad($name, 6))), 0, 12);
        return $str;
    }
}
