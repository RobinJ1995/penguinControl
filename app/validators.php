<?php

Validator::extend ('vhost_subdomain', // Check of vHost een subdomein is van de gebruiker's standaard-vHost of geen subdomein is van sinners.be //
	function ($attribute, $value, $parameters)
	{
		$username = $parameters[0];

		$endsWithSinnersBe = (substr ($value, -strlen ('sinners.be')) === 'sinners.be');
		$ownSubdomain = (substr ($value, -strlen ('.' . $username . '.sinners.be')) === '.' . $username . '.sinners.be');
		
		if ($ownSubdomain)
			return true;
		else if (! $endsWithSinnersBe)
			return true;
		
		return false;
	}
);

Validator::extend ('mail_for_uid', // Check of gebruiker eigenaar is van gebruikte e-maildomein //
	function ($attribute, $value, $parameters)
	{
		$uid = $parameters[0];
		
		$ok = false;
		
		$domains = MailDomainVirtual::where ('uid', $uid)->get ();
		foreach ($domains as $domain)
		{
			if (substr ($value, -strlen ($domain->domain)) === $domain->domain)
			    $ok = true;
		}
		
		return $ok;
	}
);
