<?php
require_once(ROOT_DIR . 'lib/Common/namespace.php');

class Reservation
{
	/**
	 * @var int
	 */
	private $_userId;

	/**
	 * @return int
	 */
	public function UserId()
	{
		return $this->_userId;
	}

	/**
	 * @var int
	 */
	private $_resourceId;

	/**
	 * @return int
	 */
	public function ResourceId()
	{
		return $this->_resourceId;
	}

	/**
	 * @var string
	 */
	private $_title;

	/**
	 * @return string
	 */
	public function Title()
	{
		return $this->_title;
	}

	/**
	 * @var string
	 */
	private $_description;

	/**
	 * @return string
	 */
	public function Description()
	{
		return $this->_description;
	}

	/**
	 * @var Date
	 */
	private $_startDate;
	
	/**
	 * @return Date
	 */
	public function StartDate()
	{
		return $this->_startDate;
	}
	
	/**
	 * @var Date
	 */
	private $_endDate;
	
	/**
	 * @return Date
	 */
	public function EndDate()
	{
		return $this->_endDate;
	}
	
	private $_repeatOptions;
	
	public function RepeatOptions()
	{
		return $this->_repeatOptions;
	}
	
	private $_repeatedDates;
	
	public function RepeatedDates()
	{
		return $this->_repeatedDates;
	}
	
	/**
	 * @param int $userId
	 * @param int $resourceId
	 * @param string $title
	 * @param string $description
	 */
	public function Update($userId, $resourceId, $title, $description)
	{
		$this->_userId = $userId;
		$this->_resourceId = $resourceId;
		$this->_title = $title;
		$this->_description = $description;
	}

	/**
	 * @param DateRange $duration
	 */
	public function UpdateDuration(DateRange $duration)
	{
		$this->_startDate = $duration->GetBegin()->ToUtc();
		$this->_endDate = $duration->GetEnd()->ToUtc();
	}
	
	public function Repeats(IRepeatOptions $repeatOptions)
	{
		$this->_repeatOptions = $repeatOptions;
		$this->_repeatedDates = $repeatOptions->GetDates();
	}
}

interface IRepeatOptions
{
	function GetDates();
}

class NoRepetion implements IRepeatOptions
{
	public function GetDates()
	{
		return array();
	}
}

class DailyRepeat implements IRepeatOptions
{
	/**
	 * @var int
	 */
	private $_interval;
	
	/**
	 * @var Date
	 */
	private $_terminiationDate;
	
	/**
	 * @var DateRange
	 */
	private $_duration;
	
	public function __construct($interval, $terminiationDate, $duration)
	{
		$this->_interval = $interval;
		$this->_terminiationDate = $terminiationDate;
		
		$this->_duration = $duration;
	}
	
	public function GetDates()
	{
		$dates = array();
		$startDate = $this->_duration->GetBegin()->AddDays($this->_interval);
		$endDate = $this->_duration->GetEnd()->AddDays($this->_interval);

//		$startDate = $reservation->StartDate()->AddDays($this->_interval);
//		$endDate = $reservation->EndDate()->AddDays($this->_interval);
		
		while ($startDate->Compare($this->_terminiationDate) <= 0)
		{
			$dates[] = new DateRange($startDate->ToUtc(), $endDate->ToUtc());
			$startDate = $startDate->AddDays($this->_interval);
			$endDate = $endDate->AddDays($this->_interval);
		}
		
		return $dates;
	}
}
	
class WeeklyRepeat implements IRepeatOptions
{
	/**
	 * @var int
	 */
	private $_interval;
	
	/**
	 * @var Date
	 */
	private $_terminiationDate;
	
	/**
	 * @var DateRange
	 */
	private $_duration;
	
	/**
	 * @var array
	 */
	private $_daysOfWeek;
	
	public function __construct($interval, $terminiationDate, $duration, $daysOfWeek)
	{
		$this->_interval = $interval;
		$this->_terminiationDate = $terminiationDate;
		
		$this->_duration = $duration;
		$this->_daysOfWeek = $daysOfWeek;
		sort($this->_daysOfWeek);
	}
	
	public function GetDates()
	{
		$dates = array();
		
		$startDate = $this->_duration->GetBegin();
		$endDate = $this->_duration->GetEnd();
		
		$startWeekday = $startDate->Weekday();
		foreach ($this->_daysOfWeek as $weekday)
		{
			if ($startWeekday < $weekday)
			{
				$startDate = $startDate->AddDays($weekday - $startWeekday);
				$endDate = $endDate->AddDays($weekday - $startWeekday);
				
				$dates[] = new DateRange($startDate->ToUtc(), $endDate->ToUtc());
			}
		}
		
//		$startDate = $startDate->AddDays($daysUntilFirstInterval);
//		$endDate = $endDate->AddDays($daysUntilFirstInterval);
		
		//$intervalAdjustment = "+{$this->_interval} weeks monday";
		//$s = strtotime("+1 weeks monday", $startDate->Timestamp());
		//$d = date('Y-m-d H:i:s', $s);
		//$startDate = new Date(date('Y-m-d H:i:s', $s), $startDate->Timezone());
		
		$days = array (0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5=> 'friday', 6 => 'saturday');
				
		$baseStart = $this->_duration->GetBegin();
		$baseEnd = $this->_duration->GetEnd();
		$intervalIteration = 1;
		
		while ($startDate->Compare($this->_terminiationDate) <= 0)
		{
			$interval = $this->_interval * $intervalIteration;
			
			$intervalAdjustment = "+$interval weeks {$days[$this->_daysOfWeek[0]]}";
			$s = strtotime($intervalAdjustment, $baseStart->Timestamp());
			$e = strtotime($intervalAdjustment, $baseEnd->Timestamp());
			
			$startDate = new Date(date('Y-m-d H:i:s', $s), $baseStart->Timezone());
			$endDate = new Date(date('Y-m-d H:i:s', $e), $baseEnd->Timezone());
			
			if (($startDate->Compare($this->_terminiationDate) <= 0))
			{
				$dates[] = new DateRange($startDate->ToUtc(), $endDate->ToUtc());
			}
			
			for ($day = 1; $day < count($this->_daysOfWeek); $day++)
			{
				$intervalAdjustment = "+$interval weeks {$days[$day]}";
				$s = strtotime($intervalAdjustment, $baseStart->Timestamp());
				$e = strtotime($intervalAdjustment, $baseEnd->Timestamp());
				
				$startDate = new Date(date('Y-m-d H:i:s', $s), $baseStart->Timezone());
				$endDate = new Date(date('Y-m-d H:i:s', $e), $baseEnd->Timezone());
				
				if ($startDate->Compare($this->_terminiationDate) <= 0)
				{
					$dates[] = new DateRange($startDate->ToUtc(), $endDate->ToUtc());
				}
			
			}
			
			$intervalIteration++;
			
//			$iterationAdjustment = (7 * $this->_interval * $intervalIteration);
//			$startDate = new Date($baseDate->Timestamp());
//			$endDate = $startDate->AddDays($iterationAdjustment);
//			
//			foreach ($this->_daysOfWeek as $weekday)
//			{
//				$dayAdjustment = $weekday - $startWeekday - 1;
//				$startDate = $startDate->AddDays($dayAdjustment);
//				$endDate = $endDate->AddDays($dayAdjustment);
//					
//				if ($startDate->Compare($this->_terminiationDate) <= 0)
//				{
//					$dates[] = new DateRange($startDate->ToUtc(), $endDate->ToUtc());
//				}
//				
//			}
//			$intervalIteration++;
//			$dates[] = new DateRange($startDate->ToUtc(), $endDate->ToUtc());
//			$startDate = $startDate->AddDays($this->_interval);
//			$endDate = $endDate->AddDays($this->_interval);
		}

		return $dates;
	}
}
?>