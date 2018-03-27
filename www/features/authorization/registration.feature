@authorization @registration
Feature: Registration form
    Anonymous users should have a possibility to register in properly.
    I need to be able to register in properly when I fill proper credential
    
#    Scenario: See login form
#        Given I go to the website root
#          And I click "Login" link
#         Then I should be on "/en/login/"
#         Then I click "Registration" link
#          And I should see "Registration" in the "legend" element
#
#    Scenario: Register properly and redirect to profile
#        Given I am on "/en/registration/"
#          And I fill in "registration_email" with "qq@qq.qq"
#          And I fill in "registration_plainPassword_first" with "qqqqqq"
#          And I fill in "registration_plainPassword_second" with "qqqqqq"
#          And I press "Registration"
#         Then I should be redirected to the "security_login" page


#    Scenario: Registration email taken
#        Given I am on "/en/registration/"
#        Given there are following users:
#          | email | password | nickName |
#          | reg@reg.qq | $2y$12$UiZ.0/etZd87PmdU1fGYs.6cRPLUX.WPHAGAkkeHedSJNlN6clIAm | User_Reg |
#          And I fill in "registration_email" with "reg@reg.qq"
#          And I fill in "registration_plainPassword_first" with "qwerty"
#          And I fill in "registration_plainPassword_second" with "qwerty"
#          And I press "Registration"
#         Then I should see error message "Email already"

#    Scenario: Registration incorrect data
#        Given I am on "/en/registration/"
#          And I fill in "registration_email" with "not email"
#          And I press "Registration"
#         Then I should see error message "email"
#         Then I fill in "registration_email" with ""
#          And I press "Registration"
#         Then I should see error message "not be blank"
#         Then I fill in "registration_email" with "qww@ww.ww"
#          And I fill in "registration_plainPassword_first" with "qwerty"
#          And I fill in "registration_plainPassword_second" with "ytrewq"
#          And I press "Registration"
#         Then I should see error message "not match"
#         Then I fill in "registration_email" with "qww@ww.ww"
#          And I fill in "registration_plainPassword_first" with "qwe"
#          And I fill in "registration_plainPassword_second" with "qwe"
#          And I press "Registration"
#         Then I should see error message "too short"

