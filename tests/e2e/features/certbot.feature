Feature: Requesting certificates
  A container cannot complete an ACME challenge, so certbot is a stub that
  records the arguments it was handed. What is worth pinning down is the command
  the panel builds and that the system task plumbing reaches it at all.

  Background:
    Given the panel is freshly installed
    And I am logged in as "penguin"

  Scenario: Asking for HTTPS queues and runs a certificate request
    When I create a vHost "secure.example.test" with document root "public_html" and HTTPS
    And the system task runner runs until the queue is idle
    Then a system task of type "vhost_obtain_certificate" was recorded with exit code 0
    And the certbot stub was invoked for "secure.example.test"
    And the certbot invocation used the Apache authenticator
    And Apache accepts its configuration
