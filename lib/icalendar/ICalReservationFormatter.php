<?php
/**
* Formats a Reservation for iCalendar
* @author Nick Korbel <lqqkout13@users.sourceforge.net>
* @version 04-24-06
* @package phpScheduleIt.iCalendar
*
* Copyright (C) 2003 - 2006 phpScheduleIt
* License: GPL, see LICENSE
*/

$basedir = dirname(__FILE__) . '/..';
require_once($basedir . '/interfaces/IReservationFormatter.php');
require_once($basedir . '/helpers/StringBuilder.class.php');

class ICalReservationFormatter extends IReservationFormatter
{
	var $_reservation;

	function ICalReservationFormatter() {
		
	}
	
	function setReservation(&$reservation) {
		$this->_reservation = $reservation;
	}
	
	function format() {
		$builder = new StringBuilder();

		$builder->append("BEGIN:VEVENT\n");
		$builder->append($this->formatSettings());
		$builder->append($this->formatOwner());
		$builder->append($this->formatParticipants());
		$builder->append($this->formatSummary());
		$builder->append($this->formatReminder());
		$builder->append($this->formatResources());
		$builder->append("END:VEVENT\n");

		return $builder->toString();
	}

	function formatSettings() {
		$builder = new StringBuilder();

		$builder->append("UID:{$this->_reservation->id}\n");
		
		$adjusted_start = Time::getAdjustedMinutes($this->_reservation->start);
		$builder->append( sprintf(
							"DTSTART:%sT%s%s00Z\n", 
							date('Ymd', Time::getAdjustedDate($this->_reservation->start_date, $this->_reservation->start)), 
							Time::getHours($adjusted_start), 
							Time::getMinutes($adjusted_start)
							) );
		
		$adjusted_end = Time::getAdjustedMinutes($this->_reservation->end);
		$builder->append( sprintf(
							"DTEND:%sT%s%s00Z\n", 
							date('Ymd', Time::getAdjustedDate($this->_reservation->end_date, $this->_reservation->end)), 
							Time::getHours($adjusted_end), 
							Time::getMinutes($adjusted_end)
							));
		
		$adjusted = Time::getAdjustedTime($this->_reservation->created);
		$builder->append( sprintf(
							"CREATED:%sT%sZ\n", 
							date('Ymd', $adjusted), 
							date('His', $adjusted)
							));
		
		if (!empty($this->_reservation->modified)) {
			$adjusted = Time::getAdjustedTime($this->_reservation->modified);
			$builder->append( sprintf(
								"LAST-MODIFIED:%sT%sZ\n", 
								date('Ymd', $adjusted), 
								date('His', $adjusted)
								));
		}
		
		return $builder->toString();
	}

	function formatOwner() {
		$builder = new StringBuilder();

		$builder->append("ORGANIZER:MAILTO:{$this->_reservation->user->email}\n");

		return $builder->toString();
	}

	function formatParticipants() {
		$builder = new StringBuilder();

		for ($i = 0; $i < count($this->_reservation->users); $i++) {
			$builder->append("ATTENDEE:MAILTO:{$this->_reservation->users[$i]['email']}\n");
		}

		return $builder->toString();
	}

	function formatSummary() {
		$builder = new StringBuilder();
		
		$summary = (!empty($this->_reservation->summary)) ? $this->_reservation->summary : $this->_reservation->resource->properties['name'];		
		$builder->append("SUMMARY:$summary\n");

		return $builder->toString();
	}

	function formatReminder() {
		$builder = new StringBuilder();
		
		if ($this->_reservation->reminder_minutes_prior != 0) {
			$builder->append("BEGIN:VALARM\n");
			$builder->append("ACTION:EMAIL\n");
			$builder->append("TRIGGER:-P{$this->_reservation->reminder_minutes_prior}M\n");
			$builder->append("END:VALARM\n");
		}

		return $builder->toString();
	}

	function formatResources() {
		$builder = new StringBuilder();

		$builder->append("RESOURCES:{$this->_reservation->resource->properties['name']}");

		for ($i = 0; $i < count($this->_reservation->resources); $i++) {
			$builder->append(",{$this->_reservation->resources[$i]['name']}");
		}

		$builder->append("\nLOCATION:{$this->_reservation->resource->properties['location']}\n");

		return $builder->toString();
	}
}
?>