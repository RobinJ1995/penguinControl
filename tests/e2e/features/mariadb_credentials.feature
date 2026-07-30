Feature: Per-user MariaDB accounts
  The panel gives each user a DBMS account for their own databases. This is the
  one place where current MariaDB genuinely broke the old code: since 10.2.4 the
  default sql_mode carries NO_AUTO_CREATE_USER, so a GRANT naming an account
  that does not exist fails instead of creating it.

  Background:
    Given the panel is freshly installed

  Scenario: Signing in provisions the user's DBMS account
    When I log in as "penguin" with password "penguin"
    Then I am on "/user/start"
    And a DBMS account "penguin" exists
    And the DBMS account "penguin" can authenticate with password "penguin"
    And the DBMS account "penguin" is granted its own database

  Scenario: The databases page issues a second, panel-managed account
    Given I am logged in as "penguin"
    When I visit "/database"
    Then the response status is 200
    And a DBMS account "pc_u5001" exists
