<?php
/**
Copyright 2011-2013 Nick Korbel

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


class CalendarMonth implements ICalendarSegment
{
	private $month;
	private $year;
	private $timezone;
	private $firstDay;
	private $lastDay;

	/**
	 * @var array|CalendarWeek[]
	 */
	private $weeks = array();

	/**
	 * @var array|CalendarReservation[]
	 */
	private $reservations = array();

	public function __construct($month, $year, $timezone)
	{
		$this->month = $month;
		$this->year = $year;
		$this->timezone = $timezone;

		$this->firstDay = Date::Create($this->year, $this->month, 1, 0, 0, 0, $this->timezone);
		$this->lastDay = $this->firstDay->AddMonths(1);

		$daysInMonth = $this->lastDay->AddDays(-1)->Day();

		$weeks = floor(($daysInMonth + $this->firstDay->Weekday()-1) / 7);

		for ($week = 0; $week <= $weeks; $week++)
		{
			$this->weeks[$week] = new CalendarWeek($timezone);
		}

		for ($dayOffset = 0; $dayOffset < $daysInMonth; $dayOffset++)
		{
			$currentDay = $this->firstDay->AddDays($dayOffset);
			$currentWeek = $this->GetWeekNumber($currentDay);
			$calendarDay = new CalendarDay($currentDay);

			echo $currentDay->Format('Ymd') . ' w=' .$currentWeek . ' ';
			$this->weeks[$currentWeek]->AddDay($calendarDay);
		}
	}

	public function Weeks()
	{
		return $this->weeks;
	}

	public function FirstDay()
	{
		return $this->firstDay;
	}

	public function LastDay()
	{
		return $this->lastDay;
	}

	/**
	 * @param $reservations array|CalendarReservation[]
	 * @return void
	 */
	public function AddReservations($reservations)
	{
		/** @var $reservation CalendarReservation */
		foreach ($reservations as $reservation)
		{
			$this->reservations[] = $reservation;

			/** @var $week CalendarWeek */
			foreach ($this->Weeks() as $week)
			{
				$week->AddReservation($reservation);
			}
		}
	}

	/**
	 * @param Date $day
	 * @return int
	 */
	private function GetWeekNumber(Date $day)
	{
		$firstWeekday = $this->firstDay->Weekday();

		$week = floor($day->Day()/7);

		if ($day->Day()%7==0)
		{
			$week = ($day->Day()-1)/7;
		}
		else
		{
			if ($day->Weekday() < $firstWeekday)
			{
				$week++;
			}
		}

		return intval($week);
	}


	/**
	 * @return string|CalendarTypes
	 */
	public function GetType()
	{
		return CalendarTypes::Month;
	}

	/**
	 * @return Date
	 */
	public function GetPreviousDate()
	{
		return $this->FirstDay()->AddMonths(-1);
	}

	/**
	 * @return Date
	 */
	public function GetNextDate()
	{
		return $this->FirstDay()->AddMonths(1);
	}

	/**
	 * @return array|CalendarReservation[]
	 */
	public function Reservations()
	{
		return $this->reservations;
	}
}

?>