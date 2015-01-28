<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSchema;
use TrainingCompany\QueryBundle\Entity\Configuration;

class FollowupController extends Controller
{
    /**
     * Send new invitations to users with unfinished surveys
     * @Route("/admin/followup/{schemaid}", name="_admin_followup")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:followup.html.twig")
     */
    public function followupAction($schemaid) {
        $em = $this->getDoctrine()->getManager();
        /* @var $schema QSchema */
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $schemaid));
        $surveys = $this->getSurveys($schemaid);
        return array('schema' => $schema, 'surveys' => $surveys, 'show' => true);
    }

    /**
     * Send new invitations to users with unfinished surveys
     * @Route("/admin/followup/send/{schemaid}", name="_admin_send_followup")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:followup.html.twig")
     */
    public function doFollowupAction(Request $request, $schemaid) {
        $em = $this->getDoctrine()->getManager();
        /* @var $schema QSchema */
        $schema = $em->getRepository(Configuration::SchemaRepo())->findOneBy(array('id' => $schemaid));
        $surveys = $this->getSurveys($schemaid);
        foreach ($surveys as $survey) {
            $this->sendFollowupMail($request, $schema, $survey);
        }
        return array('schema' => $schema, 'surveys' => $surveys, 'show' => false);
    }

    private function getSurveys($schemaid) {
        $em = $this->getDoctrine()->getManager();
        return
            $em->createQuery(
                "select s.id,p.name,p.email,s.state,s.date,s.token ".
                "from ".Configuration::SurveyRepo()." s, ".
                        Configuration::PersonRepo()." p ".
                "where s.sid=:schema and s.pid=p.id and s.state in (:invited, :ongoing) ".
                "order by p.name asc,s.state desc")
                ->setParameter('schema', $schemaid)
                ->setParameter('invited', QSurveys::$STATE_INVITED)
                ->setParameter('ongoing', QSurveys::$STATE_ONGOING)
                ->getResult();
    }
    
    private function sendFollowupMail(Request $request, QSchema $schema, $survey) {
        $parms = array(
            'from' => $schema->getEmail(),
            'sender' => $schema->getSigner(),
            'name' => $survey['name'],
            'email' => $survey['email'],
            'token' => $survey['token'],
            'date' => $survey['date'],
            'survey_visited' => $survey['state'] != QSurveys::$STATE_INVITED,
            'host' => 'http://'.$request->getHost());
        $message = Swift_Message::newInstance()
            ->setSubject("Opfølgning: ".$schema->getInvitation())
            ->setFrom(array($schema->getEmail() => $schema->getSigner()))
            ->setTo(array($survey['email'] => $survey['name']))
            ->setBody($this->renderView('TrainingCompanyQueryBundle:Mail:followupmail.html.twig', $parms), 'text/html');
        $this->get('mailer')->send($message);
    }
}
