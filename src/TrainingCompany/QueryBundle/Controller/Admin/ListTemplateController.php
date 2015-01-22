<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TrainingCompany\QueryBundle\Entity\Configuration;

class ListTemplateController extends Controller
{
    /**
     * List the available templates
     * @Route("/admin/list/templates", name="_admin_template_list")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:listtemplates.html.twig")
     */
    public function listTemplatesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $qschemas = $em->getRepository(Configuration::SchemaRepo())->findAll();
        return array('templates' => $qschemas);
    }
}
