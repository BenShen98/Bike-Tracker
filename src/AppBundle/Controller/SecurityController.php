<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2016/3/12
 * Time: 22:18
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error == null)
        {
            $response = new RedirectResponse($this->generateUrl('about'));
            return $response->send();
        }else{

            return $this->render(
                'security/login.html.twig',
                array(
                    // last username entered by the user
                    'last_username' => $lastUsername,
                    'error'         => $error,
                )
            );
        }
    }


}