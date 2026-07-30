Feature: Signing in
  penguinControl does not use Laravel's authentication scaffolding. It verifies
  the password with crypt() against the shadow-style hash in user.crypt, because
  the same hash is served to the system through NSS, and only then calls
  Auth::login(). A failed attempt re-renders the form with 200 and an alert in
  the body rather than redirecting, which is what these scenarios pin down.

  Background:
    Given the panel is freshly installed

  Scenario: The site root sends an anonymous visitor to the login form
    When I visit "/"
    Then I am on "/user/login"
    And the page mentions "Username"

  Scenario: Signing in with the right password
    When I log in as "penguin" with password "penguin"
    Then I am on "/user/start"
    And the page mentions "Welcome, Pingu!"
    And the log table records "User logged in"

  Scenario: An unknown username is reported on the form itself
    When I log in as "nobody" with password "whatever"
    Then the response status is 200
    And the page mentions "Invalid username"
    And the page does not mention "Welcome"

  Scenario: A wrong password is reported on the form itself
    When I log in as "penguin" with password "not-the-password"
    Then the response status is 200
    And the page mentions "Invalid password for user penguin"
    And the log table records "Login attempt with wrong password"

  Scenario: Signing out
    Given I am logged in as "penguin"
    When I log out
    Then I am on "/user/login"

  Scenario: An administrator reaches the staff area
    Given I am logged in as the administrator
    When I visit "/staff/user/user"
    Then the response status is 200
    And the page mentions "Users"
