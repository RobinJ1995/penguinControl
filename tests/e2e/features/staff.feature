Feature: The staff area
  Every staff screen was still in Dutch before this change, and several of them
  referred to tables and columns that no longer exist, so these scenarios walk
  the whole area.

  Background:
    Given the panel is freshly installed
    And I am logged in as the administrator

  Scenario: Every page the menu offers renders
    When I visit "/user/start"
    Then every page reachable from the menu renders

  Scenario: The system check runs over the whole install
    When I visit "/staff/maintenance/system/check"
    Then the log table records "System check executed"

  Scenario: A registration waits for validation
    When I log out
    And I register as "newcomer" with e-mail "newcomer@example.test"
    Then a user_info row exists for "newcomer" that is not yet validated
    And the log table records "Account registration"

  Scenario: The billing log starts empty on a fresh install
    When I visit "/staff/user/log"
    Then the response status is 200
    And the billing log lists 0 entries
