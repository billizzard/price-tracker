@authorization @login
Feature: Login form
    Anonymous users should have a possibility to log in properly.
    As a anonymous user
    I need to be able to log in propetly when I fill proper credential

#    Scenario: See login form
#        Given there are following users:
#          | email | password | nickName |
#          | qq1@qq.qq | $2y$12$UiZ.0/etZd87PmdU1fGYs.6cRPLUX.WPHAGAkkeHedSJNlN6clIAm | User_1 |
#        Given I go to the website root
#          And I click "Login" link
#         Then I should be on "/en/login/"
#          And I should see "Authorization" in the "legend" element
#
#    Scenario: Login properly from homepage and redirect to homepage
#        Given I am on "/en/login/"
#          And I fill in "username" with "qq1@qq.qq"
#          And I fill in "password" with "qqqqqq"
#          And I press "Login"
#         Then I should be on "/en/profile/trackers/"

#    Scenario: Login incorrect data
#        Given I am on "/en/login/"
#        Given there are following users:
#            | email | password | nickName |
#            | log@log.qq | $2y$12$UiZ.0/etZd87PmdU1fGYs.6cRPLUX.WPHAGAkkeHedSJNlN6clIAm | User_Log |
#        And I fill in "username" with "reg@reg.qq"
#        And I fill in "password" with "123321"
#        And I press "Login"
#       Then I should see error message "Incorrect"
#       Then I fill in "username" with ""
#        And I fill in "password" with ""
#        And I press "Login"
#       Then I should see error message "Incorrect"
