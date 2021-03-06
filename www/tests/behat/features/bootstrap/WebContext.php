<?php
namespace App\Tests\behat\features\bootstrap;

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
        $this->getSession()->visit('/en/');
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
        
        $this->assertSession()->elementTextContains('xpath', '//div[@class="alert-' . $class . '"]', $this->fixStepArgument($message));
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in "(?P<element>[^"]*)" with id "(?P<id>[^"]+)"$/
     */
    public function iShouldSeeInWithId($text, $element, $id)
    {
        $this->assertSession()->elementTextContains('xpath', '//' . $element . '[@id="' . $id . '"]', $this->fixStepArgument($text));
    }

// NEW
    /**
     * @Then /^I switch to iframe "([^"]*)"$/
     */
    public function iSwitchToIframe($iframeName)
    {
        if($iframeName == "")
        {
            $this->getSession()->switchToIFrame(null);
        }
        else
        {
            $this->getSession()->switchToIFrame($iframeName);
        }
    }

//    /**
//     * Attaches file to field with specified id|name|label|value
//     * Example: When I attach "bwayne_profile.png" to "profileImageUpload"
//     * Example: And I attach "bwayne_profile.png" to "profileImageUpload"
//     *
//     * @When /^(?:|I )attach the file "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)"$/
//     */
//    public function attachFileToField($field, $path)
//    {
//        $field = $this->fixStepArgument($field);
//
//        if ($this->getMinkParameter('files_path')) {
//            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
//            if (is_file($fullPath)) {
//                $path = $fullPath;
//            }
//        }
//
//        $this->getSession()->getPage()->attachFileToField($field, $path);
//    }


//    /**
//     * @Then /^I wait for "(?P<sec>\d+)" seconds$/
//     */
//    public function iWaitForSeconds($sec)
//    {
//        $this->getSession()->wait($sec * 1000);
//    }

    // END NEW


}
