<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use TrainingCompany\QueryBundle\Entity\Admin\NewPerson;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSchema;
use TrainingCompany\QueryBundle\Entity\Configuration;

class InviteController extends Controller
{
    /**
     * Administrationsside
     * @Route("/admin/invite/{schemaid}", name="_admin_invite")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:invite.html.twig")
     */
    public function inviteAction(Request $request, $schemaid) {
        $em = $this->getDoctrine()->getManager();
        /* @var $schema QSchema */
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $schemaid));
        
        $formData = new NewPerson();
        $formDef = $this->createFormBuilder($formData);
        $formDef->add('name', 'text', array('label' => 'Navn', 'required' => false));
        $formDef->add('email', 'text', array('label' => 'E-mail', 'required' => false));
        $formDef->add('invite', 'submit', array('label' => 'Inviter',
                                                'translation_domain' => 'admin',
                                                'icon' => 'fa fa-envelope-o'));
        $form = $formDef->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $formData = $form->getData();
            $qpersons = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('email' => $formData->email));
            if (!$qpersons) {
                $qpersons = new QPersons();
                $qpersons->setName($formData->name);
                $qpersons->setEmail($formData->email);
                $qpersons->setUsername($formData->email);
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($qpersons);
                $password = $encoder->encodePassword($formData->email, $qpersons->getSalt());
                $qpersons->setPassword($password);
                $em->persist($qpersons);
                $em->flush();
            }
            $qsurvey = new QSurveys();
            $qsurvey->setPid($qpersons->getId());
            $qsurvey->setSid($schemaid);
            $qsurvey->setQno(0);
            $qsurvey->setDate(time());
            $qsurvey->setState(0);
            $qsurvey->setToken($this->generateToken($formData->email));
            $em->persist($qsurvey);
            $em->flush();

            $parms = array(
                'from' => $schema->getEmail(),
                'sender' => $schema->getSigner(),
                'name' => $qpersons->getName(),
                'email' => $qpersons->getEmail(),
                'token' => $qsurvey->getToken(),
                'host' => 'http://'.$request->getHost());
            $message = Swift_Message::newInstance()
                ->setSubject($schema->getInvitation())
                ->setFrom(array($schema->getEmail() => $schema->getSigner()))
                ->setTo(array($qpersons->getEmail() => $qpersons->getName()))
                ->setBody($this->renderView('TrainingCompanyQueryBundle:Mail:invitemail.html.twig', $parms), 'text/html');
//                    ->addPart($this->renderView('TrainingCompanyQueryBundle:Default:invitemail.html.twig', $parms));
            $this->get('mailer')->send($message);
            return array(
                'form' => $form->createView(),
                'send_name' => $qpersons->getName(),
                'send_email' => $qpersons->getEmail(),
                'send_token' => $qsurvey->getToken(),
                'schema' => $schema);
        }

        return array(
            'form' => $form->createView(),
            'schema' => $schema);
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
