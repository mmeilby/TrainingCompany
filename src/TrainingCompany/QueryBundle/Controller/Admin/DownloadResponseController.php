<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TrainingCompany\QueryBundle\Entity\Configuration;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;

class DownloadResponseController extends Controller
{
    /**
     * List the available responses by survey
     * @Route("/admin/download/responses/file/{schemaid}", name="_admin_response_download_file")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function downloadFileAction($schemaid)
    {
        $em = $this->getDoctrine()->getManager();
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $schemaid));
        $outputar = $this->getResponses($schemaid);
        $tmpfname = tempnam("/tmp", "ttc-test_response_");

        $fp = fopen($tmpfname, "w");
        foreach ($outputar as $output) {
            fputs($fp, iconv("UTF-8", "ISO-8859-1", $output));
            fputs($fp, "\r\n");
        }
        fclose($fp);
        
        $response = new BinaryFileResponse($tmpfname);
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                str_replace(' ', '-', $schema->getName()).'_'.date("j-m-Y").'.txt');
        return $response;
    }
    
    private function getResponses($schemaid) {
        $surveys = $this->getSurveyInfo($schemaid);
        $outputar = array();
        $summary = array();
        foreach ($surveys as $survey) {
            $date = date("j-M-Y", $survey['date']);
            $outputstr = $survey['id'].';'.$date.';"'.$survey['name'].'";"'.$survey['email'].'";"'.$survey['jobtitle'].'";"'.$survey['jobdomain'].'"';
            $answers = $this->getAnswers($survey['id']);
            $comments = $this->getComments($survey['id']);
            $responses = array();
            foreach ($answers as $answer) {
                $responses[$answer['qno']] = $answer;
                $summary[$answer['qno']]['label'] = html_entity_decode($answer['label']);
                $summary[$answer['qno']]['sum'] += $answer['answer'];
            }
            foreach ($comments as $comment) {
                $responses[$comment['qno']] = $comment;
            }
            ksort($responses);
            foreach ($responses as $response) {
                if (array_key_exists('answer', $response)) {
                    $outputstr = $outputstr.';'.$response['answer'];
                }
                else {
                    $outputstr = $outputstr.';"'.$response['comment'].'"';
                }
            }
            $outputar[$survey['id']] = $outputstr;
        }
        ksort($summary);
        $outputar[] = '"SUMMARY"';
        foreach ($summary as $response) {
            $outputar[] = '"'.$response['label'].'";'.$response['sum'];
        }
        return $outputar;
    }
    
    private function getSurveyInfo($schemaid) {
        $em = $this->getDoctrine()->getManager();
        return
            $em->createQuery(
                    "select s.id,s.date,p.name,p.email,p.jobtitle,p.jobdomain ".
                    "from ".Configuration::SurveyRepo()." s ".
                    "inner join ".
                            Configuration::PersonRepo()." p ".
                    "with s.pid=p.id ".
                    "where s.sid=:schema and s.state=:completed ".
                    "order by s.state asc, s.date asc")
                ->setParameter('schema', $schemaid)
                ->setParameter('completed', QSurveys::$STATE_FINISHED)
                ->getResult();
    }
    
    private function getAnswers($surveyid) {
        $em = $this->getDoctrine()->getManager();
        return
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
    }
    
    private function getComments($surveyid) {
        $em = $this->getDoctrine()->getManager();
        return
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
    }
}
