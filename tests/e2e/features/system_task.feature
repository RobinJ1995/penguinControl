Feature: The system task queue
  Privileged work is queued in the system_task table and drained by
  `php artisan cron:run`, which the installation instructions put in root's
  crontab. These scenarios exercise the queue the way cron does.

  Background:
    Given the panel is freshly installed

  Scenario: Creating a vHost queues its document root and an Apache reload
    Given I am logged in as "penguin"
    When I create a vHost "tasked.example.test" with document root "public_html/tasked"
    And the system task runner runs until the queue is idle
    Then a system task of type "create_vhost_docroot" was recorded with exit code 0
    And the directory "/home/penguin/public_html/tasked" exists and belongs to "penguin"
    And Apache accepts its configuration

  Scenario: An Apache reload task really reloads Apache
    Given I am logged in as the administrator
    When I schedule a "apache_reload" system task
    And the system task runner runs
    Then a system task of type "apache_reload" was recorded with exit code 0

  Scenario: Disk usage is calculated for every live user
    Given I am logged in as the administrator
    When I schedule a "calculate_disk_usage" system task
    And the system task runner runs
    Then a system task of type "calculate_disk_usage" was recorded with exit code 0
    And the disk usage of "penguin" has been calculated
