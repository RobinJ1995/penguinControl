Feature: The interface is in English
  README used to describe the application as part-way through being translated.
  This guards the finished translation: it walks every page the menu offers, as
  an administrator and as an ordinary user, and fails on any Dutch it finds in
  the rendered text.

  Background:
    Given the panel is freshly installed

  Scenario: No Dutch remains anywhere an administrator can reach
    Given I am logged in as the administrator
    When I visit "/user/start"
    Then no page reachable from the menu contains Dutch text

  Scenario: No Dutch remains anywhere an ordinary user can reach
    Given I am logged in as "penguin"
    When I visit "/user/start"
    Then no page reachable from the menu contains Dutch text
