<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TrainingCompany\QueryBundle\Entity\Doctrine\QPersons;
use TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys;
use TrainingCompany\QueryBundle\Entity\Configuration;

class ListUserController extends Controller
{
    /**
     * List the users related to a club
     * @Route("/admin/list/users", name="_admin_user_list")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:listusers.html.twig")
     */
    public function listUsersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $qpersons = $em->getRepository(Configuration::PersonRepo())->findAll();
        return array('users' => $qpersons);
    }
}
