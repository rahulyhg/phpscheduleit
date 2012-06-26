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

require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');

require_once(ROOT_DIR . 'Pages/ReservationPage.php');
require_once(ROOT_DIR . 'Pages/NewReservationPage.php');
require_once(ROOT_DIR . 'Pages/ExistingReservationPage.php');

require_once(ROOT_DIR . 'lib/Application/Reservation/ExistingReservationInitializer.php');

class ExistingReservationInitializerTests extends TestBase
{
	/**
	 * @var int
	 */
	private $userId;


	public function setup()
	{
		parent::setup();

		$this->userId = $this->fakeUser->UserId;
	}

	public function teardown()
	{
		parent::teardown();
	}

	public function testExistingReservationIsLoadedAndBoundToView()
	{
		$scheduleId = 1;

		$userBinder = $this->getMock('IReservationComponentBinder');
		$dateBinder = $this->getMock('IReservationComponentBinder');
		$resourceBinder = $this->getMock('IReservationComponentBinder');
		$reservationBinder = $this->getMock('IReservationComponentBinder');
		$attributeBinder = $this->getMock('IReservationComponentBinder');
		$page = $this->getMock('IExistingReservationPage');

		$reservationView = new ReservationView();

		$initializer = new ExistingReservationInitializer(
			$page,
			$userBinder,
			$dateBinder,
			$resourceBinder,
			$reservationBinder,
			$attributeBinder,
			$reservationView,
			$this->fakeUser);

		$reservationView->ScheduleId = $scheduleId;

		$page->expects($this->once())
				->method('SetScheduleId')
				->with($this->equalTo($scheduleId));

		$userBinder->expects($this->once())
				->method('Bind')
				->with($this->equalTo($initializer));

		$dateBinder->expects($this->once())
				->method('Bind')
				->with($this->equalTo($initializer));

		$resourceBinder->expects($this->once())
				->method('Bind')
				->with($this->equalTo($initializer));

		$reservationBinder->expects($this->once())
				->method('Bind')
				->with($this->equalTo($initializer), $this->equalTo($page), $this->equalTo($reservationView));

		$initializer->Initialize();
	}

	public function testBindsToClosestPeriodFromReservationDates()
	{
		$page = $this->getMock('IExistingReservationPage');
		$binder = $this->getMock('IReservationComponentBinder');

		$timezone = $this->fakeUser->Timezone;

		$dateString = Date::Now()->AddDays(1)->SetTimeString('02:55:22')->Format('Y-m-d H:i:s');
		$endDateString = Date::Now()->AddDays(1)->SetTimeString('4:55:22')->Format('Y-m-d H:i:s');
		$dateInUserTimezone = Date::Parse($dateString, $timezone);

		$startDate = Date::Parse($dateString, $timezone);
		$endDate = Date::Parse($endDateString, $timezone);

		$expectedStartPeriod = new SchedulePeriod($dateInUserTimezone->SetTime(new Time(3, 30, 0)), $dateInUserTimezone->SetTime(new Time(4, 30, 0)));
		$expectedEndPeriod = new SchedulePeriod($dateInUserTimezone->SetTime(new Time(4, 30, 0)), $dateInUserTimezone->SetTime(new Time(7, 30, 0)));
		$periods = array(
			new SchedulePeriod($dateInUserTimezone->SetTime(new Time(1, 0, 0)), $dateInUserTimezone->SetTime(new Time(2, 0, 0))),
			new SchedulePeriod($dateInUserTimezone->SetTime(new Time(2, 0, 0)), $dateInUserTimezone->SetTime(new Time(3, 0, 0))),
			new NonSchedulePeriod($dateInUserTimezone->SetTime(new Time(3, 0, 0)), $dateInUserTimezone->SetTime(new Time(3, 30, 0))),
			$expectedStartPeriod,
			$expectedEndPeriod,
			new SchedulePeriod($dateInUserTimezone->SetTime(new Time(7, 30, 0)), $dateInUserTimezone->SetTime(new Time(17, 30, 0))),
			new SchedulePeriod($dateInUserTimezone->SetTime(new Time(17, 30, 0)), $dateInUserTimezone->SetTime(new Time(0, 0, 0))),
		);

		$page->expects($this->once())
				->method('SetSelectedStart')
				->with($this->equalTo($expectedStartPeriod), $this->equalTo($startDate));

		$page->expects($this->once())
				->method('SetSelectedEnd')
				->with($this->equalTo($expectedEndPeriod), $this->equalTo($endDate));

		$reservationView = new ReservationView();
		$reservationView->StartDate = $startDate;
		$reservationView->EndDate = $endDate;

		$anything = Date::Now();

		$initializer = new ExistingReservationInitializer(
			$page,
			$binder,
			$binder,
			$binder,
			$binder,
			$binder,
			$reservationView,
			$this->fakeUser);
		$initializer->SetDates($anything, $anything, $periods);
	}
}

?>