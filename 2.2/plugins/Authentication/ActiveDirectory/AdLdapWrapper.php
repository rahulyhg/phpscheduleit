<?php
/**
Copyright 2011-2012 Nick Korbel

This file is part of phpScheduleIt.

phpScheduleIt is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

phpScheduleIt is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with phpScheduleIt.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(ROOT_DIR . 'plugins/Authentication/ActiveDirectory/adLDAP.php');

class AdLdapWrapper implements IActiveDirectory
{
	/**
	 * @var ActiveDirectoryOptions
	 */
	private $options;

	/**
	 * @var adLdap|null
	 */
	private $ldap;

	/**
	 * @param ActiveDirectoryOptions $ldapOptions
	 */
	public function __construct($ldapOptions)
	{
		$this->options = $ldapOptions;
	}

	public function Connect()
	{
		$connected = false;
		$attempts = 0;
		$hosts = $this->options->Controllers();
		$options = $this->options->AdLdapOptions();

		while (!$connected && $attempts < count($hosts))
		{
			try
			{
				$host = $hosts[$attempts];
				Log::Debug('ActiveDirectory - Trying to connect to host %s', $host);
				$options['domain_controllers'] = array($host);
				$attempts++;
				$this->ldap = new adLdap($options);
				$connected = true;

				if ($connected)
				{
					Log::Debug('ActiveDirectory - Connection succeeded to host %s', $host);
				}
			}
			catch (adLDAPException $ex)
			{
				Log::Error($ex);
                throw($ex);
			}
		}

		return $connected;
	}

	public function Authenticate($username, $password)
	{
		$authenticated =  $this->ldap->authenticate($username, $password);
		if (!$authenticated)
		{
			Log::Debug('ActiveDirectory - Authenticate for user %s failed with reason %s', $username, $this->ldap->getLastError());
		}
		return $authenticated;
	}

	public function GetLdapUser($username)
	{
		$attributes = array( 'sn', 'givenname', 'mail', 'telephonenumber', 'physicaldeliveryofficename', 'title' );

		/** @var adLDAPUserCollection $entries  */
		$entries = $this->ldap->user()->infoCollection($username, $attributes);
		if (count($entries) > 0)
		{
			return new ActiveDirectoryUser($entries);
		}
		else
		{
			Log::Debug('ActiveDirectory - Could not load user details for user %s. Reason %s', $username, $this->ldap->getLastError());
		}

		return null;
	}
}
?>