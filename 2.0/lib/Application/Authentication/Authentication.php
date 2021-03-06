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


require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'lib/Common/namespace.php');
require_once(ROOT_DIR . 'lib/Database/namespace.php');
require_once(ROOT_DIR . 'lib/Database/Commands/namespace.php');
require_once(ROOT_DIR . 'Domain/Values/RoleLevel.php');

class Authentication implements IAuthentication
{
    /**
     * @var PasswordMigration
     */
    private $passwordMigration = null;

    /**
     * @var IAuthorizationService
     */
    private $authorizationService;

    public function __construct(IAuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function SetMigration(PasswordMigration $migration)
    {
        $this->passwordMigration = $migration;
    }

    /**
     * @return PasswordMigration
     */
    private function GetMigration()
    {
        if (is_null($this->passwordMigration))
        {
            $this->passwordMigration = new PasswordMigration();
        }

        return $this->passwordMigration;
    }

    public function Validate($username, $password)
    {
        Log::Debug('Trying to log in as: %s', $username);

        $command = new AuthorizationCommand($username);
        $reader = ServiceLocator::GetDatabase()->Query($command);
        $valid = false;

        if ($row = $reader->GetRow())
        {
            Log::Debug('User was found: %s', $username);
            $migration = $this->GetMigration();
            $password = $migration->Create($password, $row[ColumnNames::OLD_PASSWORD], $row[ColumnNames::PASSWORD]);
            $salt = $row[ColumnNames::SALT];

            if ($password->Validate($salt))
            {
                $password->Migrate($row[ColumnNames::USER_ID]);
                $valid = true;
            }
        }

        Log::Debug('User: %s, was validated: %d', $username, $valid);
        return $valid;
    }

	/**
	 * @param string $username
	 * @param ILoginContext $loginContext
	 */
    public function Login($username, $loginContext)
    {
        Log::Debug('Logging in with user: %s', $username);

        $command = new LoginCommand($username);
        $reader = ServiceLocator::GetDatabase()->Query($command);

        if ($row = $reader->GetRow())
        {
			$loginData = $loginContext->GetData();
            $loginTime = LoginTime::Now();
            $userid = $row[ColumnNames::USER_ID];
            $emailAddress = $row[ColumnNames::EMAIL];
            $language = $row[ColumnNames::LANGUAGE_CODE];

			if (!empty($loginData->Language))
			{
				$language = $loginData->Language;
			}

            $isAdminRole = $this->IsAdminRole($userid, $emailAddress);

            $updateLoginTimeCommand = new UpdateLoginDataCommand($userid, $loginTime, $language);
            ServiceLocator::GetDatabase()->Execute($updateLoginTimeCommand);

            $this->SetUserSession($row, $isAdminRole, $loginContext->GetServer());

            if ($loginContext->GetData()->Persist)
            {
                $this->SetLoginCookie($userid, $loginTime, $loginContext->GetServer());
            }
        }
    }

    public function Logout(UserSession $userSession)
    {
        Log::Debug('Logout userId: %s', $userSession->UserId);

        $this->DeleteLoginCookie($userSession->UserId);
        ServiceLocator::GetServer()->SetSession(SessionKeys::USER_SESSION, null);

        @session_unset();
        @session_destroy();
    }

    public function CookieLogin($cookieValue, $loginContext)
    {
        $loginCookie = LoginCookie::FromValue($cookieValue);
        $valid = false;

        if (!is_null($loginCookie))
        {
            $validEmail = $this->ValidateCookie($loginCookie);
            $valid = !is_null($validEmail);

            if ($valid)
            {
                $this->Login($validEmail, $loginContext);
            }
        }

        return $valid;
    }

    public function AreCredentialsKnown()
    {
        return false;
    }

    public function HandleLoginFailure(ILoginPage $loginPage)
    {
        $loginPage->SetShowLoginError();
    }

    private function IsAdminRole($userId, $emailAddress)
    {
        return $this->authorizationService->IsApplicationAdministrator(new AuthorizationUser($userId, $emailAddress));
    }

	/**
	 * @param array $row
	 * @param bool $isAdminRole
	 * @param Server $server
	 */
    private function SetUserSession($row, $isAdminRole, $server)
    {
        $user = new UserSession($row[ColumnNames::USER_ID]);
        $user->Email = $row[ColumnNames::EMAIL];
        $user->FirstName = $row[ColumnNames::FIRST_NAME];
        $user->LastName = $row[ColumnNames::LAST_NAME];
        $user->Timezone = $row[ColumnNames::TIMEZONE_NAME];
        $user->HomepageId = $row[ColumnNames::HOMEPAGE_ID];

        $isAdmin = ($user->Email == Configuration::Instance()->GetKey(ConfigKeys::ADMIN_EMAIL)) || (bool)$isAdminRole;
        $user->IsAdmin = $isAdmin;

		$server->SetUserSession($user);
    }

	/**
	 * @param int $userid
	 * @param string $lastLogin
	 * @param Server $server
	 */
    private function SetLoginCookie($userid, $lastLogin, $server)
    {
        $cookie = new LoginCookie($userid, $lastLogin);
		$server->SetCookie($cookie);
    }

    private function DeleteLoginCookie($userid)
    {
        $cookie = new LoginCookie($userid, null);
        ServiceLocator::GetServer()->SetCookie($cookie);
    }

    private function ValidateCookie($loginCookie)
    {
        $valid = false;
        $reader = ServiceLocator::GetDatabase()->Query(new CookieLoginCommand($loginCookie->UserID));

        if ($row = $reader->GetRow())
        {
            $valid = $row[ColumnNames::LAST_LOGIN] == $loginCookie->LastLogin;
        }

        return $valid ? $row[ColumnNames::EMAIL] : null;
    }

}

?>