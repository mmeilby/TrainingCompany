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
        $person = $em->getRepository(Configuration::PersonRepo())->findOneBy(array('id' => $survey->getPid()));
        $answers =
            $em->createQuery(
                    "select r.qno,b.qpage,b.qtype,r.answer,d.value,b.label ".
                    "from ".Configuration::ResponseRepo()." r ".
                    "inner join ".
                            Configuration::SurveyRepo()." s ".
                    "with r.qid=s.id ".
                    "inner join ".
                            Configuration::BlockRepo()." b ".
                    "with b.qno=r.qno and b.sid=s.sid ".
                    "left join ".
                            Configuration::DomainRepo()." d ".
                    "with d.qbid=b.id and d.domain=r.answer ".
                    "where r.qid=:survey ".
                    "order by r.qno asc")
                ->setParameter('survey', $surveyid)
                ->getResult();
        $comments =
            $em->createQuery(
                    "select c.qno,b.qpage,b.qtype,c.comment,b.label ".
                    "from ".Configuration::CommentRepo()." c ".
                    "inner join ".
                            Configuration::SurveyRepo()." s ".
                    "with c.qid=s.id ".
                    "inner join ".
                            Configuration::BlockRepo()." b ".
                    "with b.qno=c.qno and b.sid=s.sid ".
                    "where c.qid=:survey ".
                    "order by c.qno asc")
                ->setParameter('survey', $surveyid)
                ->getResult();
        $responses = array();
        foreach ($answers as $answer) {
            $responses[$answer['qpage']][$answer['qno']] = $answer;
        }
        foreach ($comments as $comment) {
            $responses[$comment['qpage']][$comment['qno']] = $comment;
        }
        ksort($responses);
        foreach ($responses as $response) {
            ksort($response);
        }
        return array('responses' => $responses, 'subject' => $schema->getName()."/".$person->getName());
    }
}
