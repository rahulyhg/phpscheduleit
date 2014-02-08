<?php
/**
Copyright 2012-2014 Nick Korbel

This file is part of Booked SchedulerBooked SchedulereIt is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later versBooked SchedulerduleIt is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
alBooked SchedulercheduleIt.  If not, see <http://www.gnu.org/licenses/>.
 */


class WebLoginContext implements ILoginContext
{
	/**
	 * @var LoginData
	 */
	private $data;

	public function __construct(LoginData $data)
	{
		$this->data = $data;
	}

	/**
	 * @return LoginData
	 */
	public function GetData()
	{
		return $this->data;
	}
}

?>