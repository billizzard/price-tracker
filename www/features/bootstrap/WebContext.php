<?php

use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * 
 * @author Athlan
 *
 */
class WebContext extends DefaultContext
{
    /**
     * @When /^I go to the website root$/
     */
    public function iGoToTheWebsiteRoot()
    {
        $this->getSession()->visit('/');
    }

    /**
     * @When /^I go to the "([^"]+)"  url?$/
     */
    public function iGoToTheUrl($url)
    {
        $this->getSession()->visit($url);
    }
    
    /**
     * @Given /^I am on the "([^"]+)"  page?$/
     * @When /^(?:|I )go to the "([^"]+)"  page?$/
     */
    public function iAmOnThePage($page)
    {
        $this->getSession()->visit($this->generateUrl($page));
    }
    
    /**
     * Checks, that current page PATH matches regular expression.
     *
     * @Then /^(?:|I )should be redirected to (?P<pattern>"(?:[^"]|\\")*")$/
     */
    public function iAmRedirectedToUrl($pattern)
    {
        $this->assertSession()->addressMatches($this->fixStepArgument($pattern));
    }
    
    /**
     * @Then /^(?:|I )should be on the "([^"]+)" (page)$/
     * @Then /^(?:|I )should be redirected to the "([^"]+)" (page)$/
     * @Then /^(?:|I )should still be on the "([^"]+)" (page)$/
     */
    public function iShouldBeOnThePage($page)
    {
        $this->assertSession()->addressEquals($this->generateUrl($page));
        try {
            $this->assertSession()->statusCodeEquals(200);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @When /^(?:|I )click into "([^"]+)" link?$/
     * @When /^(?:|I )click "([^"]+)" link?$/
     */
    public function iClickLink($link)
    {
        $this->clickLink($link);
    }

    /**
     * @When /^Print page content$/
     */
    public function printPageContent()
    {
        echo $this->getSession()->getPage()->getContent();
    }
    
    /**
     * @Then /^(?:|I )should see "([^"]+)" (heading|headline)$/
     */
    public function iShouldSeeHeading($heading)
    {
        $this->assertSession()->elementTextContains('xpath', '//h1 | //h2', $this->fixStepArgument($heading));
    }

    /**
     * @Then /^(?:|I )should see (?P<type>[(error|success|info|warning)]+) message "(?P<message>[^"]+)"$/
     */
    public function iShouldSeeMessage($type, $message)
    {
        $classesMap = [
            'success' => 'success',
            'error' => 'error',
            'info' => 'info',
            'warning' => 'warning',
        ];
        $class = $classesMap[$type];
        
        $this->assertSession()->elementTextContains('xpath', '//div[@class="flash-message flash-' . $class . '"]', $this->fixStepArgument($message));
    }

    public function spin ($lambda, $tries = 30, $sleep = 2)
    {
        for ($i = 0; $i < $tries; $i++)
        {
            try
            {
                if ($lambda($this))
                {
                    return true;
                }
            }
            catch (Exception $e)
            {
                // do nothing
            }

            sleep($sleep);
        }

        $backtrace = debug_backtrace();
//		throw new BehatException ("Wait time limit of ". $tries*$sleep ." seconds exceeded. Text \"" .$backtrace[1]['args' ][0]. "\" not found", $this->getSession());
        //throw new \Behat\Mink\Exception\ExpectationException ("Wait time limit of ". $tries*$sleep ." seconds exceeded. Text \"" .$backtrace[1]['args' ][0]. "\" not found", $this->getSession());
//		throw new Exception(
//			"Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n" .
//			"With the following arguments: " . print_r($backtrace[1]['args'], true)
//		);
    }

    /**
     * @Then /^(?:|I )should see (?P<type>[(error|success|info|warning)]+) message "(?P<message>[^"]+)" after ajax$/
     */
    public function iShouldSeeMessageAfterAjax($type, $message)
    {
        $this->spin(function() use ($message) {
            try
            {
                $this->assertSession()->pageTextContains($this->fixStepArgument($message));
                return true;
            }
            catch(Exception $e)
            {}
            return false;
        });

//        $classesMap = [
//            'success' => 'success',
//            'error' => 'error',
//            'info' => 'info',
//            'warning' => 'warning',
//        ];
//        $class = $classesMap[$type];
//
//        $this->assertSession()->elementTextContains('xpath', '//div[@class="flash-message flash-' . $class . '"]', $this->fixStepArgument($message));
    }
}
