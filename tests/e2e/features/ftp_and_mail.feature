Feature: FTP accounts and mail
  Both are consumed by other daemons straight out of these tables -- an FTP
  server reads ftp, and Postfix reads mail_domain, mail_user and mail_forward --
  so the column names and the shape of what is written are part of the contract.

  Background:
    Given the panel is freshly installed

  Scenario: Creating an FTP account stores a crypt hash in ftp.passwd
    Given I am logged in as "penguin"
    When I create an FTP account "backups" in directory "public_html"
    Then the ftp table has a row with username "penguin_backups"

  Scenario: A staff-created mail domain and a user-created forward
    Given I am logged in as the administrator
    When I add the mail domain "penguin.example.test" for "penguin" as the administrator
    Then the mail_domain table has a row for "penguin.example.test"

  Scenario: A forward records the domain it belongs to
    Given I am logged in as the administrator
    When I add the mail domain "fwd.example.test" for "penguin" as the administrator
    And I log out
    And I log in as "penguin" with password "penguin"
    And I enable mail for my account
    And I add a mail forward "hello" to "someone@elsewhere.test" on domain "fwd.example.test"
    Then the mail_forward table has a row from "hello" to "someone@elsewhere.test"
