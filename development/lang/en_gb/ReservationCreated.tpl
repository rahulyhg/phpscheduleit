{*
Copyright  Nick Korbel

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
*}
{include file='..\tpl\Email\emailheader.tpl'}
	en_gb version
	You have successfully created a new reservation.
	<br/>
	<br/>
	
	Starting: {formatdate date=$StartDate key=reservation_email}<br/>
	Ending: {formatdate date=$EndDate key=reservation_email}<br/>
	Title: {$Title}<br/>
	Description: {$Description}<br/>
	
	{if count($RepeatDates) gt 0}
		<br/>
		Your reservation was repeated on the following dates:
		<br/>
	{/if}
	
	{foreach from=$RepeatDates item=date name=dates}
		{formatdate date=$date}<br/>
	{/foreach}
	
	<br/>
	<a href="{$ScriptUrl}{$ReservationUrl}">View this reservation</a> | <a href="{$ScriptUrl}">Log in to phpScheduleIt</a>
	
{include file='..\tpl\Email\emailfooter.tpl'}