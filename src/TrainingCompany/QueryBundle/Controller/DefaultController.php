<?php
namespace TrainingCompany\QueryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Test indgang til brugerundersøgelsen
     * @Route("/", name="_home")
     * @Method("GET")
     * @Template("TrainingCompanyQueryBundle:Default:home.html.twig")
     */
    public function homeAction() {
        return array();
    }

    /**
     * Fejlside til præsentation af tekniske fejl
     * @Route("/error", name="_error")
     * @Template("TrainingCompanyQueryBundle:Default:error.html.twig")
     */
    public function errorAction(Request $request) {
        $session = $request->getSession();
        $error = $session->get('error');
        return array('error' => $error);
    }
/*    
        catch (\PDOException $e) {
            $session = $request->getSession();
            $session->set('error', $e->getMessage());
            return $this->redirect($this->generateUrl('_error'));
        }
 */
}
