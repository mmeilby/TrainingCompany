<?php
namespace TrainingCompany\QueryBundle\Controller\Survey;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;

class OptoutController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/optout/{id}", name="_optout")
     * @Template("TrainingCompanyQueryBundle:Default:optout.html.twig")
     */
    public function optAction(Request $request, $id) {
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

            if ($qsurveys->getState() != QSurveys::$STATE_INVITED && $qsurveys->getState() != QSurveys::$STATE_ONGOING) {
                return $this->render(
                        'TrainingCompanyQueryBundle:Default:finished_survey.html.twig',
                        array(
                            'survey' => $qsurveys,
                            'company' => $qschema->getName()));
            }

            $qsurveys->setState(QSurveys::$STATE_OPTED);
            $qsurveys->setDate(time());
            $em->flush();
        }
        catch (\PDOException $e) {
            $session = $request->getSession();
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
                
        return array(
            'id' => $id,
            'name' => $qpersons->getName(),
            'company' => $qschema->getName(),
            'signer' => $qschema->getSigner()
        );
    }
}
