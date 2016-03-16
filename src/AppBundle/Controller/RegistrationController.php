<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2016/3/14
 * Time: 19:19
 */

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends Controller
{
    public function registerAction(Request $request)
    {
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $this->get('security.password_encoder')
                ->encodePassword($user,$user->getPassword());///->encodePassword($user, $user->getPlainPassword())
            $user->setPassword($password);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('homepage');
            /*
            return $this->render('::debug.html.twig',
                array('getdata'=>$plainPassword,
                'get'=>$form->get('Password'))
            );
            */
        }

        return $this->render(
            ':ees/registration:register.html.twig',
            array('form' => $form->createView())
        );
    }
}