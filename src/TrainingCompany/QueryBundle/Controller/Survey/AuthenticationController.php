<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

use TrainingCompany\QueryBundle\Controller\Survey\SurveyController;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;

class AuthenticationController extends Controller
{
    /**
     * Startpunkt for adgang til brugerundersøgelse via link
     * @Route("/auth/{id}", name="_referred")
     * @Method("GET")
     */
    public function authAction(Request $request, $id) {
        $session = $request->getSession();
        try {
            $em = $this->getDoctrine()->getManager();
            $qsurveys = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('token' => $id));
            if (!$qsurveys) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
            $qschema = $em->getRepository(Configuration::SchemaRepo())->find($qsurveys->getSid());
            if (!$qschema) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }

            if ($qsurveys->getState() == QSurveys::$STATE_INVITED) {
                $session->set(SurveyController::$sessionPage, 0);
            }
            else if ($qsurveys->getState() == QSurveys::$STATE_ONGOING) {
                $session->set(SurveyController::$sessionPage, $qsurveys->getQno());
            }
            else {
                return $this->render(
                        'TrainingCompanyQueryBundle:Default:finished_survey.html.twig',
                        array(
                            'survey' => $qsurveys,
                            'company' => $qschema->getName()));
            }

            $qpersons = $em->getRepository(Configuration::PersonRepo())->find($qsurveys->getPid());
            if (!$qpersons) {
                return $this->render('TrainingCompanyQueryBundle:Default:invalid_person.html.twig');
            }
            if (!$qpersons->getUsername()) {
                $qpersons->setUsername($qpersons->getEmail());
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($qpersons);
                $password = $encoder->encodePassword($qpersons->getEmail(), $qpersons->getSalt());
                $qpersons->setPassword($password);
                $em->flush();
            }
            $token = new UsernamePasswordToken($qpersons, null, 'new_user', $qpersons->getRoles());
            $this->get('security.context')->setToken($token);
            $this->get('event_dispatcher')->dispatch(
                    AuthenticationEvents::AUTHENTICATION_SUCCESS,
                    new AuthenticationEvent($token));

            $session->set(SurveyController::$sessionSurveyId, $qsurveys->getId());
            $session->set(SurveyController::$sessionTemplateId, $qsurveys->getSid());
            return $this->redirect($this->generateUrl('_start'));
        }
        catch (\PDOException $e) {
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
    }
}
