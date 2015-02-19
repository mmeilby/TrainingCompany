<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

use TrainingCompany\QueryBundle\Entity\Admin\NewPerson;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSchema;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;

use Swift_Message;

class SelfAuthenticationController extends Controller
{
    /**
     * Startpunkt for adgang til brugerundersøgelse via link
     * @Route("/sauth/{tag}", name="_self_authentication")
     * @Template("TrainingCompanyQueryBundle:Default:sauth.html.twig")
     */
    public function selfAuthenticateAction(Request $request, $tag) {
        $em = $this->getDoctrine()->getManager();
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('tag' => $tag));
        if (!$schema) {
            return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
        }

        $form = $this->makeForm();
        $form->handleRequest($request);
        if ($this->validateForm($form)) {
            $user = $this->makePerson($form);
            $survey = $this->makeSurvey($schema, $user);
            $this->sendMail($request, $user, $survey, $schema);
            $this->sendInfoMail($request, $user, $schema);
            return $this->render('TrainingCompanyQueryBundle:Default:sauth_ty.html.twig');
        }
        return array(
            'form' => $form->createView(),
            'schema' => $schema);
    }
    
    /**
     * Dan formular
     * @return mixed den genererede formular
     */
    private function makeForm() {
        $formData = new NewPerson();
        $formDef = $this->createFormBuilder($formData);
        $formDef->add('name', 'text', array('label' => 'FORM.AUTH.NAME.LABEL',
                                            'help' => 'FORM.AUTH.NAME.HELP',
                                            'required' => false, 
                                            'translation_domain' => 'admin',
                                            'icon' => 'fa fa-user'));
        $formDef->add('email', 'text', array('label' => 'FORM.AUTH.EMAIL.LABEL',
                                            'help' => 'FORM.AUTH.EMAIL.HELP',
                                            'required' => false,
                                            'translation_domain' => 'admin',
                                            'icon' => 'fa fa-envelope'));
        $formDef->add('department', 'text', array('label' => 'FORM.AUTH.DEPT.LABEL',
                                            'help' => 'FORM.AUTH.DEPT.HELP',
                                            'required' => false,
                                            'translation_domain' => 'admin',
                                            'icon' => 'fa fa-users'));
        $formDef->add('title', 'text', array('label' => 'FORM.AUTH.JOB.LABEL',
                                            'help' => 'FORM.AUTH.JOB.HELP',
                                            'required' => false,
                                            'translation_domain' => 'admin',
                                            'icon' => 'fa fa-graduation-cap'));
        $formDef->add('invite', 'submit', array('label' => 'FORM.AUTH.INVITE',
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-envelope-o'));
        return $formDef->getForm();
    }
    
    private function validateForm($form) {
        if ($form->isValid()) {
            $formData = $form->getData();
            if ($formData->name == null || trim($formData->name) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.AUTH.NONAME', array(), 'admin')));
            }
            if ($formData->email == null || trim($formData->email) == '') {
                $form->addError(new FormError($this->get('translator')->trans('FORM.AUTH.NOEMAIL', array(), 'admin')));
            }
            $emailConstraint = new EmailConstraint();
            $emailConstraint->checkHost = true;
            $emailConstraint->message = $this->get('translator')->trans('FORM.AUTH.INVALIDEMAIL', array(), 'admin');
            $errorList = $this->get('validator')->validateValue($formData->email, $emailConstraint);
            if (count($errorList)) {
                $form->addError(new FormError($errorList[0]->getMessage()));
            }
        }
        return $form->isValid();
    }
    
    private function makePerson($form) {
        $em = $this->getDoctrine()->getManager();
        $formData = $form->getData();
        $user = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('email' => $formData->email));
        if (!$user) {
            $user = new QPersons();
            $user->setName($formData->name);
            $user->setEmail($formData->email);
            $user->setUsername($formData->email);
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($formData->email, $user->getSalt());
            $user->setPassword($password);
            $user->setJobtitle($formData->title);
            $user->setJobdomain($formData->department);
            $em->persist($user);
            $em->flush();
        }
        return $user;
    }

    private function makeSurvey(QSchema $schema, QPersons $user) {
        $em = $this->getDoctrine()->getManager();
        $survey = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('pid' => $user->getId(), 'sid' => $schema->getId()));
        if (!$survey) {
            $survey = new QSurveys();
            $survey->setPid($user->getId());
            $survey->setSid($schema->getId());
            $survey->setQno(0);
            $survey->setDate(time());
            $survey->setState(0);
            $survey->setToken($this->generateToken($user->getEmail()));
            $em->persist($survey);
            $em->flush();
        }
        return $survey;
    }

    private function generateToken($name) {
        $str = dechex(time()).'-'.
               dechex(rand(4096, 65535)).'-'.
               dechex(rand(4096, 65535)).'-'.
               dechex(rand(4096, 65535)).'-'.
               substr(bin2hex(str_shuffle(str_pad($name, 6))), 0, 12);
        return $str;
    }

    private function sendMail(Request $request, QPersons $user, QSurveys $survey, QSchema $schema) {
        $parms = array(
            'from' => $schema->getEmail(),
            'sender' => $schema->getSigner(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'token' => $survey->getToken(),
            'host' => 'http://'.$request->getHost());
        $message = Swift_Message::newInstance()
            ->setSubject($schema->getInvitation())
            ->setFrom(array($schema->getEmail() => $schema->getSigner()))
            ->setTo(array($user->getEmail() => $user->getName()))
            ->setBody($this->renderView('TrainingCompanyQueryBundle:Mail:ny_invitemail.html.twig', $parms), 'text/html');
        $this->get('mailer')->send($message);
    }
    
    private function sendInfoMail($request, $user, $schema) {
        $parms = array(
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'host' => $request->getHost(),
            'schema' => $schema->getName());
        $message = Swift_Message::newInstance()
            ->setSubject($this->container->getParameter('sauth-title'))
            ->setFrom(array($this->container->getParameter('mailuser') => $this->container->getParameter('admin-name')))
            ->setTo(array($this->container->getParameter('feedback-mail') => $this->container->getParameter('feedback-name')))
            ->setBody($this->renderView('TrainingCompanyQueryBundle:Mail:sauthmail.html.twig', $parms), 'text/html');
        $this->get('mailer')->send($message);
    }
}
