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

require_once(ROOT_DIR . 'plugins/Authentication/Ldap/namespace.php');

class LdapTests extends TestBase
{
	/**
	 * @var FakeAuth
	 */
	private $fakeAuth;

	/**
	 * @var FakeLdapOptions
	 */
	private $fakeLdapOptions;

	/**
	 * @var FakeLdapWrapper
	 */
	private $fakeLdap;

	/**
	 * @var LdapUser
	 */
	private $ldapUser;

	/**
	 * @var FakeRegistration
	 */
	private $fakeRegistration;

	/**
	 * @var FakePasswordEncryption
	 */
	private $encryption;

	private $username = 'username';
	private $password = 'password';

	/**
	 * @var ILoginContext
	 */
	private $loginContext;

	public function setup()
	{
		parent::setup();

		$this->fakeAuth = new FakeAuth();
		$this->fakeLdapOptions = new FakeLdapOptions();
		$this->fakeLdap = new FakeLdapWrapper();
		$this->fakeRegistration = new FakeRegistration();
		$this->encryption = new FakePasswordEncryption();

		$ldapEntry = new TestLdapEntry();
		$ldapEntry->Set('sn', 'user');
		$ldapEntry->Set('givenname', 'test');
		$ldapEntry->Set('mail', 'ldap@user.com');
		$ldapEntry->Set('telephonenumber', '000-000-0000');
		$ldapEntry->Set('physicaldeliveryofficename', '');
		$ldapEntry->Set('title', '');

		$this->ldapUser = new LdapUser($ldapEntry);

		$this->fakeLdap->_ExpectedLdapUser = $this->ldapUser;

		$this->loginContext = $this->getMock('ILoginContext');
	}

	public function testCanValidateUser()
	{
		$this->fakeLdapOptions->_RetryAgainstDatabase = false;
		$expectedResult = true;
		$this->fakeLdap->_ExpectedAuthenticate = $expectedResult;

		$auth = new Ldap($this->fakeAuth, $this->fakeLdap, $this->fakeLdapOptions);
		$isValid = $auth->Validate($this->username, $this->password);

		$this->assertEquals($expectedResult, $isValid);
		$this->assertTrue($this->fakeLdap->_ConnectCalled);
		$this->assertTrue($this->fakeLdap->_AuthenticateCalled);
		$this->assertEquals($this->username, $this->fakeLdap->_LastUsername);
		$this->assertEquals($this->password, $this->fakeLdap->_LastPassword);
	}

	public function testNotValidIfCannotFindUser()
	{
		$this->fakeLdap->_ExpectedLdapUser = null;
		$auth = new Ldap($this->fakeAuth, $this->fakeLdap, $this->fakeLdapOptions);
		$isValid = $auth->Validate($this->username, $this->password);

		$this->assertFalse($isValid);
		$this->assertTrue($this->fakeLdap->_ConnectCalled);
		$this->assertTrue($this->fakeLdap->_AuthenticateCalled);
	}

	public function testDoesNotTryToLoadUserDetailsIfNotValid()
	{
		$this->fakeLdap->_ExpectedAuthenticate = false;
		$auth = new Ldap($this->fakeAuth, $this->fakeLdap, $this->fakeLdapOptions);
		$isValid = $auth->Validate($this->username, $this->password);

		$this->assertFalse($this->fakeLdap->_GetLdapUserCalled);
		$this->assertFalse($isValid);
	}

	public function testNotValidIfValidateFailsAndNotFailingOverToDb()
	{
		$this->fakeLdapOptions->_RetryAgainstDatabase = false;
		$expectedResult = false;
		$this->fakeLdap->_ExpectedAuthenticate = $expectedResult;

		$auth = new Ldap($this->fakeAuth, $this->fakeLdap, $this->fakeLdapOptions);
		$isValid = $auth->Validate($this->username, $this->password);

		$this->assertEquals($expectedResult, $isValid);
	}

	public function testFailOverToDbIfConfigured()
	{
		$this->fakeLdapOptions->_RetryAgainstDatabase = true;
		$this->fakeLdap->_ExpectedAuthenticate = false;

		$authResult = true;
		$this->fakeAuth->_ValidateResult = $authResult;

		$auth = new Ldap($this->fakeAuth, $this->fakeLdap, $this->fakeLdapOptions);
		$isValid = $auth->Validate($this->username, $this->password);

		$this->assertEquals($authResult, $isValid);
		$this->assertTrue($this->fakeLdap->_AuthenticateCalled);
		$this->assertEquals($this->username, $this->fakeAuth->_LastLogin);
		$this->assertEquals($this->password, $this->fakeAuth->_LastPassword);
	}

	public function testLoginSynchronizesInfoAndCallsAuthLogin()
	{
		$timezone = 'UTC';
		$this->fakeConfig->SetKey(ConfigKeys::SERVER_TIMEZONE, $timezone);
		$languageCode = 'en_US';
		$this->fakeConfig->SetKey(ConfigKeys::LANGUAGE, $languageCode);

		$expectedUser = new AuthenticatedUser(
			$this->username,
			$this->ldapUser->GetEmail(),
			$this->ldapUser->GetFirstName(),
			$this->ldapUser->GetLastName(),
			$this->password,
			$languageCode,
			$timezone,
			$this->ldapUser->GetPhone(),
			$this->ldapUser->GetInstitution(),
			$this->ldapUser->GetTitle());


		$auth = new Ldap($this->fakeAuth, $this->fakeLdap, $this->fakeLdapOptions);
		$auth->SetRegistration($this->fakeRegistration);

		$auth->Validate($this->username, $this->password);
		$auth->Login($this->username, $this->loginContext);

		$this->assertTrue($this->fakeRegistration->_SynchronizeCalled);
		$this->assertEquals($expectedUser, $this->fakeRegistration->_LastSynchronizedUser);
		$this->assertEquals($this->loginContext, $this->fakeAuth->_LastLoginContext);
	}

	public function testDoesNotSyncIfUserWasNotFoundInLdap()
	{
		$this->fakeLdap->_ExpectedLdapUser = null;

		$auth = new Ldap($this->fakeAuth, $this->fakeLdap, $this->fakeLdapOptions);

		$auth->Validate($this->username, $this->password);
		$auth->Login($this->username, $this->loginContext);

		$this->assertFalse($this->fakeRegistration->_SynchronizeCalled);
		$this->assertEquals($this->loginContext, $this->loginContext);
	}

	public function testConstructsOptionsCorrectly()
	{
		$hosts = 'localhost, localhost.2';
		$port = '389';
		$binddn = 'cn=admin,ou=users,dc=example,dc=org';
		$password = 'pw';
		$base = 'dc=example,dc=org';
		$starttls = 'false';
		$version = '3';
		$filter = '(objectClass=*)';
		$scope = 'sub';

		$configFile = new FakeConfigFile();
		$configFile->SetKey(LdapConfig::HOST, $hosts);
		$configFile->SetKey(LdapConfig::PORT, $port);
		$configFile->SetKey(LdapConfig::BINDDN, $binddn);
		$configFile->SetKey(LdapConfig::BINDPW, $password);
		$configFile->SetKey(LdapConfig::BASEDN, $base);
		$configFile->SetKey(LdapConfig::STARTTLS, $starttls);
		$configFile->SetKey(LdapConfig::VERSION, $version);
		$configFile->SetKey(LdapConfig::FILTER, $filter);
		$configFile->SetKey(LdapConfig::SCOPE, $scope);

		$this->fakeConfig->SetFile(LdapConfig::CONFIG_ID, $configFile);

		$ldapOptions = new LdapOptions();
		$options = $ldapOptions->Ldap2Config();

		$this->assertNotNull($this->fakeConfig->_RegisteredFiles[LdapConfig::CONFIG_ID]);
		$this->assertEquals('localhost', $options['host'][0], 'domain_controllers must be an array');
		$this->assertEquals(intval($port), $options['port'], 'port should be int');
		$this->assertEquals($binddn, $options['binddn']);
		$this->assertEquals($password, $options['bindpw']);
		$this->assertEquals($base, $options['basedn']);
		$this->assertEquals(false, $options['starttls']);
		$this->assertEquals($filter, $options['filter']);
		$this->assertEquals($scope, $options['scope']);
		$this->assertEquals(intval($version), $options['version'], "version should be int");
	}

	public function testGetAllHosts()
	{
		$controllers = 'localhost, localhost.2';

		$configFile = new FakeConfigFile();
		$configFile->SetKey(LdapConfig::HOST, $controllers);
		$this->fakeConfig->SetFile(LdapConfig::CONFIG_ID, $configFile);

		$options = new LdapOptions();

		$this->assertEquals(array('localhost', 'localhost.2'), $options->Controllers(), "comma separated values should become array");
	}
}

class FakeLdapOptions extends LdapOptions
{
	public $_RetryAgainstDatabase = false;

	public function RetryAgainstDatabase()
	{
		return $this->_RetryAgainstDatabase;
	}
}

class FakeLdapWrapper extends Ldap2Wrapper
{
	public function __construct()
	{

	}

	public $_ExpectedConnect = true;
	public $_ExpectedAuthenticate = true;

	public $_AuthenticateCalled = false;
	public $_GetLdapUserCalled = false;
	public $_ConnectCalled;

	public $_LastPassword;
	public $_LastUsername;

	public $_ExpectedLdapUser;

	public function Connect()
	{
		$this->_ConnectCalled = true;
		return $this->_ExpectedConnect;
	}

	public function Authenticate($username, $password)
	{
		$this->_AuthenticateCalled = true;
		$this->_LastUsername = $username;
		$this->_LastPassword = $password;

		return $this->_ExpectedAuthenticate;
	}

	public function GetLdapUser($username)
	{
		$this->_GetLdapUserCalled = true;

		return $this->_ExpectedLdapUser;
	}
}

class TestLdapEntry extends Net_LDAP2_Entry
{
	private $_values = array();

	public function __construct()
	{

	}

	public function getValue($attr)
	{
		return $this->_values[$attr];
	}

	public function Set($attr, $value)
	{
		$this->_values[$attr] = $value;
	}
}

?>