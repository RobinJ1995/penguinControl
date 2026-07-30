<?php

namespace App\Provisioning;

/**
 * Raised when the panel refuses to generate configuration for a server.
 *
 * Note that App\AppException, despite the name, is not throwable -- it is a view model
 * that carries an exception's details into a Blade template, because a real Exception
 * cannot be passed through ->with (). The controllers that catch \Exception and wrap it
 * in one of those will handle this the same as any other failure.
 */
class ProvisioningException extends \RuntimeException
{
}
