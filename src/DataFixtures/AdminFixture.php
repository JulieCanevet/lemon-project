<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $admin = new Admin();
        // $manager->persist($product);
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'the_new_password'
       ));
        $manager->flush();
    }
}
