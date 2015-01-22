<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TrainingCompany\QueryBundle\Entity\Configuration;

class ListSurveyController extends Controller
{
    /**
     * List the available surveys by user
     * @Route("/admin/list/surveys/byuser/{userid}", name="_admin_survey_list_byuser")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:listsurveys.html.twig")
     */
    public function listSurveysAction($userid)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('id' => $userid));
        $qsurveys = $em->getRepository(Configuration::SurveyRepo())->findBy(array('pid' => $userid));
        return array('surveys' => $qsurveys, 'subject' => $user->getName());
    }
    
    /**
     * List the available surveys by schema
     * @Route("/admin/list/surveys/byschema/{schemaid}", name="_admin_survey_list_byschema")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:listsurveys.html.twig")
     */
    public function listSurveysBySchemaAction($schemaid)
    {
        $em = $this->getDoctrine()->getManager();
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $schemaid));
        $qsurveys = $em->getRepository(Configuration::SurveyRepo())->findBy(array('sid' => $schemaid));
        return array('surveys' => $qsurveys, 'subject' => $schema->getName());
    }
}
