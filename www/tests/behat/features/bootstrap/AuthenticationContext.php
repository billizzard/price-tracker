<?php

use Behat\Gherkin\Node\TableNode;


/**
 * 
 * @author Athlan
 *
 */
class AuthenticationContext extends DefaultContext
{
    private $users = array();

    /**
     * @Given /^there are following users:$/
    */
    public function thereAreFollowingUsers(TableNode $table) {
        foreach ($table->getHash() as $row) {
            $entityManager = $this->kernel->getContainer()->get('doctrine')->getManager();
            /** @var \App\Repository\UserRepository $repository */
            $repository = $this->kernel->getContainer()->get('doctrine')->getRepository(\App\Entity\User::class);
            /** @var \App\Entity\User $user */
            $user = $repository->findOneBy(['email' => $row['email']]);
            if ($user) {
                $entityManager->remove($user);
                $entityManager->flush();
            }
            $user = new \App\Entity\User();
            $user->setEmail($row['email']);
            $user->setPassword($row['password']);
            $user->setNickName($row['nickName']);
            $entityManager->persist($user);
            $entityManager->flush();
        }
    }
    
    /**
     * @Given /^I am authenticated as "([^"]+)"$/
     */
    public function iAmAuthenticatedAs($email) {
        /** @var \App\Repository\UserRepository $repository */
        $repository = $this->kernel->getContainer()->get('doctrine')->getRepository(\App\Entity\User::class);
        /** @var \App\Entity\User $user */
        $user = $repository->findOneBy(['email' => $email]);
        if (!$user) {
            throw new \OutOfBoundsException('Invalid user ' . $email);
        }
        
        $this->visitPath('/en/login/');
        $this->fillField('username', $user->getEmail());
        $this->fillField('password', 'qqqqqq');
        $this->pressButton('Login');
    }
    
    /**
     * @Given /^I am not logged in$/
     * @Given /^I am (an )?anonymous user?$/
     */
    public function iAmNotLoggedIn()
    {
        $this->getSession()->visit($this->generateUrl('fos_user_security_logout'));
    }
}
