<?php
namespace TrainingCompany\QueryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller
{
    /**
     * @Route("/admin/login", name="_admin_login")
     * @Template("TrainingCompanyQueryBundle:Default:login.html.twig")
     */
    public function loginAction(Request $request)
    {
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        $form = $this->makeLoginForm($request);
        $form->handleRequest($request);
        
        return array(
            'form'          => $form->createView(),
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error
        );
    }

    private function makeLoginForm(Request $request) {
        $formDef = $this->createFormBuilder(array('username' => $request->getSession()->get(SecurityContext::LAST_USERNAME)));
        $formDef->setAction($this->generateUrl('_security_check'));
        $formDef->add('username', 'text', array('label' => 'Brugernavn',
                                                'translation_domain' => 'club',
                                                'required' => false,
                                                'help' => 'Indtast dit brugernavn',
                                                'icon' => 'fa fa-user'));
        $formDef->add('password', 'password', array('label' => 'Kodeord', 
                                                    'translation_domain' => 'club',
                                                    'required' => false,
                                                    'help' => 'Indtast kodeord',
                                                    'icon' => 'fa fa-key'));
        $formDef->add('login', 'submit', array('label' => 'Log ind',
                                               'translation_domain' => 'club',
                                               'icon' => 'fa fa-sign-in'));
        return $formDef->getForm();
    }
    
    /**
     * @Route("/admin/login_check", name="_security_check")
     */
    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/admin/logout", name="_admin_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }
}
