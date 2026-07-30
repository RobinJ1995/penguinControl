Feature: Generating Apache vHosts
  This is the compatibility claim that matters most: the vHost penguinControl
  writes has to be something current Apache will actually load. The generated
  file uses AssignUserID, an mpm-itk directive, and php_admin_value open_basedir,
  which only mod_php honours -- so these scenarios check the file's contents,
  that Apache parses it, and that the site it describes really serves.

  Background:
    Given the panel is freshly installed
    And I am logged in as "penguin"

  Scenario: Creating a vHost writes a configuration file Apache accepts
    When I create a vHost "penguin.example.test" with document root "public_html"
    Then the vhost table has a row for "penguin.example.test"
    And a vHost file for "penguin.example.test" exists for user "penguin"
    And it is symlinked into sites-enabled
    And the vHost file contains
      """
      <VirtualHost *:80>
      ServerName penguin.example.test
      AssignUserID penguin user
      DocumentRoot "/home/penguin/public_html"
      AllowOverride All
      Require all granted
      """
    And Apache accepts its configuration

  Scenario: open_basedir confines the site to its own directories
    When I create a vHost "confined.example.test" with document root "public_html"
    Then a vHost file for "confined.example.test" exists for user "penguin"
    And the vHost file contains
      """
      php_admin_value open_basedir "/home/penguin/public_html:/home/penguin:/tmp:/usr/share/php"
      """
    And the vHost file does not contain "/usr/share/php/:"
    And Apache accepts its configuration

  Scenario: The access log goes to a directory that exists
    When I create a vHost "logged.example.test" with document root "public_html"
    Then a vHost file for "logged.example.test" exists for user "penguin"
    And the vHost file contains
      """
      CustomLog "/var/log/apache2/vhost/VHOST_penguin_logged.example.test.log" combined
      """
    And Apache accepts its configuration

  Scenario: Removing a vHost takes its configuration with it
    Given "penguin" has a vHost "gone.example.test" with document root "public_html"
    When I remove the vHost "gone.example.test"
    Then the vhost table has no row for "gone.example.test"
    And no vHost file for "gone.example.test" exists for user "penguin"
    And Apache accepts its configuration
