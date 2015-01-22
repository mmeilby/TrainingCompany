<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TrainingCompany\QueryBundle\Entity\Configuration;

class ListResponseController extends Controller
{
    /**
     * List the available responses by survey
     * @Route("/admin/list/responses/{surveyid}", name="_admin_response_list")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:listresponses.html.twig")
     */
    public function listResponsesAction($surveyid)
    {
        $em = $this->getDoctrine()->getManager();
        $survey = $em->getRepository(Configuration::SurveyRepo())->findOneBy(array('id' => $surveyid));
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $survey->getSid()));
        $qb = $em->createQuery(
         "select r.qno,r.answer,b.label ".
         "from ".Configuration::ResponseRepo()." r, ".
                 Configuration::BlockRepo()." b ".
         "where r.qid=:survey and b.qno=r.qno order by r.qno asc");
        $qb->setParameter('survey', $surveyid);
        $responses = $qb->getResult();
        return array('responses' => $responses, 'subject' => $schema->getName());
    }
}
