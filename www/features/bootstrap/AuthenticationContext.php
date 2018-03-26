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
            $user = new \App\Entity\User();
            $entityManager = $this->kernel->getContainer()->get('doctrine')->getManager();
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
    public function iAmAuthenticatedAs($username) {
        if (!isset($this->users[$username]['password'])) {
            throw new \OutOfBoundsException('Invalid user ' . $username);
        }
        
        $this->visitPath('/login');
        $this->fillField('_username', $username);
        $this->fillField('_password', $this->users[$username]['password']);
        $this->checkField('remember_me');
        $this->pressButton('_submit');
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
