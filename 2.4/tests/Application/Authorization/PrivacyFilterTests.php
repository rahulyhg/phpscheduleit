<?php
/**
Copyright 2012 Nick Korbel

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

require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');

class PrivacyFilterTests extends TestBase
{
	/**
	 * @var PrivacyFilter
	 */
	private $privacyFilter;

	/**
	 * @var IReservationAuthorization|PHPUnit_Framework_MockObject_MockObject
	 */
	private $reservationAuthorization;

	public function setup()
	{
		parent::setup();

		$this->reservationAuthorization = $this->getMock('IReservationAuthorization');

		$this->privacyFilter = new PrivacyFilter($this->reservationAuthorization);
	}

	public function testCanViewUserDetailsIfConfigured()
	{
		$this->hideUserDetails(false);

		$user = new UserSession(1);

		$canView = $this->privacyFilter->CanViewUser($user, null);

		$this->assertTrue($canView);
	}
	
	public function testHidesDetailsIfUserIsNotAnAdminAndNoReservationProvided()
	{
		$this->hideUserDetails(true);

		$user = new UserSession(1);

		$canView = $this->privacyFilter->CanViewUser($user, null);

		$this->assertFalse($canView);
	}

	public function testCanViewIfUserIsAnAdminAndNoReservationProvided()
	{
		$this->hideUserDetails(true);

		$user = new UserSession(1);
		$user->IsAdmin = true;

		$canView = $this->privacyFilter->CanViewUser($user, null);

		$this->assertTrue($canView);
	}

	public function testCanViewIfUserIsOwner()
	{
		$this->hideUserDetails(true);

		$userId = 1;
		$user = new UserSession($userId);

		$canView = $this->privacyFilter->CanViewUser($user, null, $userId);

		$this->assertTrue($canView);
	}
	
	public function testChecksReservationAuthorizationAndCachesResultIfUserNotAnAdminAndReservationProvided()
	{
		$this->hideUserDetails(true);

		$user = new UserSession(1);
		$reservation = new ReservationView();

		$this->reservationAuthorization->expects($this->once())
					->method('CanViewDetails')
					->with($this->equalTo($reservation), $this->equalTo($user))
					->will($this->returnValue(true));
		
		$canView = $this->privacyFilter->CanViewUser($user, $reservation);
		$canView2 = $this->privacyFilter->CanViewUser($user, $reservation);

		$this->assertTrue($canView);
		$this->assertTrue($canView2);
	}

	public function testCanViewReservationDetailsIfConfigured()
	{
		$this->hideReservationDetails(false);

		$user = new UserSession(1);

		$canView = $this->privacyFilter->CanViewDetails($user, null);

		$this->assertTrue($canView);
	}

	public function testHidesReservationDetailsIfUserIsNotAnAdminAndNoReservationProvided()
	{
		$this->hideReservationDetails(true);

		$user = new UserSession(1);

		$canView = $this->privacyFilter->CanViewDetails($user, null);

		$this->assertFalse($canView);
	}

	public function testCanViewReservationIfUserIsAnAdminAndNoReservationProvided()
	{
		$this->hideReservationDetails(true);

		$user = new UserSession(1);
		$user->IsAdmin = true;

		$canView = $this->privacyFilter->CanViewDetails($user, null);

		$this->assertTrue($canView);
	}

	public function testCanViewReservationIfUserIsOwner()
	{
		$this->hideReservationDetails(true);

		$userId = 1;
		$user = new UserSession($userId);

		$canView = $this->privacyFilter->CanViewDetails($user, null, $userId);

		$this->assertTrue($canView);
	}

	public function testReservationDetailsChecksReservationAuthorizationAndCachesResultIfUserNotAnAdminAndReservationProvided()
	{
		$this->hideReservationDetails(true);

		$user = new UserSession(1);
		$reservation = new ReservationView();

		$this->reservationAuthorization->expects($this->once())
					->method('CanViewDetails')
					->with($this->equalTo($reservation), $this->equalTo($user))
					->will($this->returnValue(true));

		$canView = $this->privacyFilter->CanViewDetails($user, $reservation);
		$canView2 = $this->privacyFilter->CanViewDetails($user, $reservation);

		$this->assertTrue($canView);
		$this->assertTrue($canView2);
	}

	private function hideUserDetails($hide)
	{
		$this->fakeConfig->SetSectionKey(ConfigSection::PRIVACY, ConfigKeys::PRIVACY_HIDE_USER_DETAILS, $hide);
	}

	private function hideReservationDetails($hide)
	{
		$this->fakeConfig->SetSectionKey(ConfigSection::PRIVACY, ConfigKeys::PRIVACY_HIDE_RESERVATION_DETAILS, $hide);
	}
}

?>