<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TrainingCompany\QueryBundle\Entity\Configuration;

class ListSchemaController extends Controller
{
    /**
     * List the available schemas
     * @Route("/admin/list/schemas", name="_admin_schema_list")
     * @Method("GET")
     * @Secure(roles="ROLE_ADMIN")
     * @Template("TrainingCompanyQueryBundle:Admin:listschemas.html.twig")
     */
    public function listSchemasAction()
    {
        $em = $this->getDoctrine()->getManager();
        $schemas = $em->getRepository(Configuration::SchemaRepo())->findAll();
        return array('schemas' => $schemas);
    }
}
