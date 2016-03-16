<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2016/3/12
 * Time: 19:07
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\User;

class addUser extends Controller implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $userAdmin = new User();
        $userAdmin->setUsername('Ben');
        $userAdmin->setEmail('shenleyang007@com');

        $plainPassword = '123456';
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($userAdmin, $plainPassword);

        $userAdmin->setPassword($encoded);

        $manager->persist($userAdmin);
        $manager->flush();
    }
}