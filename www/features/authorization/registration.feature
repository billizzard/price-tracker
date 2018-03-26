@authorizationLogin
Feature: Registration form
    Anonymous users should have a possibility to register in properly.
    I need to be able to register in properly when I fill proper credential
    
    Scenario: See login form
        Given I go to the website root
          And I click "Login" link
         Then I should be on "/en/login/"
         Then I click "Registration" link
          And I should see "Registration" in the "legend" element

    Scenario: Register properly and redirect to profile
      Given I am on "/en/registration/"
        And I fill in "registration_email" with "qq@qq.qq"
        And I fill in "registration_plainPassword_first" with "qqqqqq"
        And I fill in "registration_plainPassword_second" with "qqqqqq"
        And I press "Registration"
       Then I should be redirected to the "security_login" page
