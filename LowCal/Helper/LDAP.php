<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class LDAP
 * @package LowCal\Helper
 */
class LDAP
{
	/**
	 * @param string $username
	 * @param string $password
	 * @return array
	 * @throws \Exception
	 */
	public static function ldapAuthenticate(string $username, string $password): array
	{
		$dn = Config::get('APP_LDAP_DN');

		$ldapDN = 'uid='.$username.','.$dn;

		$ldapCONN   = ldap_connect(Config::get('APP_LDAP_HOST').":".Config::get('APP_LDAP_PORT'));

		if(!empty($ldapCONN))
		{
			ldap_set_option($ldapCONN, LDAP_OPT_PROTOCOL_VERSION, 3);

			$ldapBIND = ldap_bind($ldapCONN, $ldapDN, $password);

			if($ldapBIND)
			{
				$result = ldap_search($ldapCONN, $dn, "(uid=".$username.")");

				if(!empty($result))
				{
					$data = ldap_get_entries($ldapCONN, $result);

					if(!empty($data))
					{
						return $data;
					}
					else
					{
						throw new \Exception(ldap_error($ldapCONN), Codes::LDAP_DATA_FETCH_FAILED);
					}
				}
				else
				{
					throw new \Exception(ldap_error($ldapCONN), Codes::LDAP_SEARCH_FAILED);
				}
			}
			else
			{
				throw new \Exception('Unable to bind to LDAP server.', Codes::LDAP_BIND_FAILED);
			}
		}
		else
		{
			throw new \Exception('Unable to connect to LDAP server.', Codes::LDAP_CONNECT_FAILED);
		}
	}
}