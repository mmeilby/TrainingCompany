<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TrainingCompany\QueryBundle\Entity\Configuration;

class DownloadResponseController extends Controller
{
    /**
     * List the available responses by survey
     * @Route("/admin/download/responses/{schemaid}", name="_admin_response_download")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:downloadresponses.html.twig")
     */
    public function downloadResponsesAction($schemaid)
    {
        $em = $this->getDoctrine()->getManager();
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $schemaid));
        $surveys =
            $em->createQuery(
                    "select s.id,s.state,s.date,p.name,p.email ".
                    "from ".Configuration::SurveyRepo()." s ".
                    "inner join ".
                            Configuration::PersonRepo()." p ".
                    "with s.pid=p.id ".
                    "where s.sid=:schema ".
                    "order by s.state asc, s.date asc")
                ->setParameter('schema', $schemaid)
                ->getResult();
        $outputar = array();
        foreach ($surveys as $survey) {
            $outputstr = $survey['id'].";".$survey['state'].";".$survey['date'].";".$survey['name'].";".$survey['email'];
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
                    ->setParameter('survey', $survey['id'])
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
                    ->setParameter('survey', $survey['id'])
                    ->getResult();
            $responses = array();
            foreach ($answers as $answer) {
                $responses[$answer['qno']] = $answer;
            }
            foreach ($comments as $comment) {
                $responses[$comment['qno']] = $comment;
            }
            ksort($responses);
            foreach ($responses as $response) {
                if (array_key_exists('answer', $response)) {
                    $outputstr = $outputstr.";".$response['answer'];
                }
                else {
                    $outputstr = $outputstr.";".$response['comment'];
                }
            }
            $outputar[$survey['id']] = $outputstr;
        }
        return array('output' => $outputar, 'subject' => $schema->getName());
    }
}
