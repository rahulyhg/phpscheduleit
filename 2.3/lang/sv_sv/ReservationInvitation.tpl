{*
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
*}
{include file='..\..\tpl\Email\emailheader.tpl'}
	Bokningsdetaljer:
	<br/>
	<br/>
	
	Bokningen börjar: {formatdate date=$StartDate key=reservation_email}<br/>
	Bokningen slutar: {formatdate date=$EndDate key=reservation_email}<br/>
	Bokning: {$ResourceName}<br/>
	Rubrik: {$Title}<br/>
	Beskrivning: {$Description|nl2br}<br/>
	
	{if count($RepeatDates) gt 0}
		<br/>
		Bokning har gjorts följande datum:
		<br/>
	{/if}
	
	{foreach from=$RepeatDates item=date name=dates}
		{formatdate date=$date}<br/>
	{/foreach}

	{if $RequiresApproval}
		<br/>
		Denna bokning behöver godkännas innan den börjar gälla.  Denna bokning är reserverad tills den är godkänd.
	{/if}
	
	<br/>
	Deltar? <a href="{$ScriptUrl}/{$AcceptUrl}">Ja</a> <a href="{$ScriptUrl}/{$DeclineUrl}">Nej</a>
	<br/>

	<a href="{$ScriptUrl}/{$ReservationUrl}">Visa denna bokning</a> |
	<a href="{$ScriptUrl}/{$ICalUrl}">Lägg till i Outlook</a> |
	<a href="{$ScriptUrl}">Logga in i Bokningsprogrammet</a>
	
{include file='..\..\tpl\Email\emailfooter.tpl'}