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


class TestBase extends PHPUnit_Framework_TestCase
{
	/**
	 * @var FakeDatabase
	 */
	public $db;
	
	/**
	 * @var FakeServer
	 */
	public $fakeServer;
	
	/**
	 * @var FakeConfig
	 */
	public $fakeConfig;
	
	/**
	 * @var FakeResources
	 */
	public $fakeResources;
	
	/**
	 * @var FakeEmailService
	 */
	public $fakeEmailService;
	
	/**
	 * @var UserSession
	 */
	public $fakeUser;
	
	public function setup()
	{
		Date::_SetNow(Date::Now());
		
		$this->db = new FakeDatabase();
		$this->fakeServer = new FakeServer();
		$this->fakeEmailService = new FakeEmailService();
		$this->fakeConfig = new FakeConfig();
        $this->fakeConfig->SetKey(ConfigKeys::SERVER_TIMEZONE, 'America/Chicago');
                
		$this->fakeResources = new FakeResources();
		
		$this->fakeUser = $this->fakeServer->UserSession;
		
		ServiceLocator::SetDatabase($this->db);
		ServiceLocator::SetServer($this->fakeServer);
		ServiceLocator::SetEmailService($this->fakeEmailService);
		Configuration::SetInstance($this->fakeConfig);
		Resources::SetInstance($this->fakeResources);
	}
	
	public function teardown()
	{
		$this->db = null;
		$this->fakeServer = null;
		Configuration::SetInstance(null);
		$this->fakeResources = null;
		Date::_ResetNow();
	}
}
?>