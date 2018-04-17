<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('qq@qq.qq');
        $user->setPassword('$2y$12$A1comgHtNSnZwjz09PIWhuD2DOt2iV8rwG74pZ9t9apQzkwdpYByC');//qq
        $manager->persist($user);
        $manager->flush();
    }
}
//php bin/console doctrine:fixtures:load
