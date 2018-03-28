@profile @user
Feature: User form
    User should have a possibility change email, nickName, password.

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

    Scenario: Profile change user data
        Given there are following users:
            | email | password | nickName |
            | user@user.qq | $2y$12$UiZ.0/etZd87PmdU1fGYs.6cRPLUX.WPHAGAkkeHedSJNlN6clIAm | User_User |
        And I am authenticated as "user@user.qq"
       Then I go to the "/en/profile/user/"  url
        And I should see "User_User" in the ".widget-user-username" element
        And I fill in "form_nickName" with "aa"
        And I press "save"
       Then I should see error message "too short" after ajax
       Then I fill in "form_nickName" with "Some name"
        And I fill in "form_email" with "qqqq"
        And I press "save"
       Then I should see error message "email" after ajax

    Scenario: Profile user change password
        Given there are following users:
          | email | password | nickName |
          | user@user.qq | $2y$12$UiZ.0/etZd87PmdU1fGYs.6cRPLUX.WPHAGAkkeHedSJNlN6clIAm | User_User |
        And I am authenticated as "user@user.qq"
        Then I go to the "/en/profile/user/"  url
        And I should see "User_User" in the ".widget-user-username" element
        Then I fill in "form_nickName" with "Some name"
        And I fill in "form_email" with "qqqq"

        And I press "Login"
       Then I should see error message "Incorrect"
       Then I fill in "username" with ""
        And I fill in "password" with ""
        And I press "Login"
       Then I should see error message "Incorrect"
