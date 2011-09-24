<?php
require_once(ROOT_DIR . 'Domain/namespace.php');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');

require_once(ROOT_DIR . 'Pages/ReservationPage.php');

abstract class ReservationInitializerBase implements IReservationInitializer
{
	/**
	 * @var IReservationPage
	 */
	protected $basePage;
	
	/**
	 * @var IScheduleUserRepository
	 */
	protected $scheduleUserRepository;
	
	/**
	 * @var IScheduleRepository
	 */
	protected $scheduleRepository;
	
	/**
	 * @var IUserRepository
	 */
	protected $userRepository;

	/**
	 * @var int
	 */
	protected $currentUserId;

	/**
	 * @param $page IReservationPage
	 * @param $scheduleUserRepository IScheduleUserRepository
	 * @param $scheduleRepository IScheduleRepository
	 * @param $userRepository IUserRepository
	 */
	public function __construct(
		$page, 
		IScheduleUserRepository $scheduleUserRepository,
		IScheduleRepository $scheduleRepository,
		IUserRepository $userRepository
		)
	{
		$this->basePage = $page;
		$this->scheduleUserRepository = $scheduleUserRepository;
		$this->scheduleRepository = $scheduleRepository;
		$this->userRepository = $userRepository;
	}
	
	public function Initialize()
	{
		$currentUser = ServiceLocator::GetServer()->GetUserSession();
		$this->currentUserId = $currentUser->UserId;
				
		$requestedResourceId = $this->GetResourceId();
		$requestedScheduleId = $this->GetScheduleId();
		$reservationDate = $this->GetReservationDate();
		$requestedStartDate = $this->GetStartDate();
		$requestedEndDate = $this->GetEndDate();
		
		$userId = $this->GetOwnerId();
		$timezone = $this->GetTimezone();
		
		$requestedDate = ($reservationDate == null) ? Date::Now()->ToTimezone($timezone) : $reservationDate->ToTimezone($timezone);
		
		$startDate = ($requestedStartDate == null) ? $requestedDate : $requestedStartDate->ToTimezone($timezone);
		$endDate = ($requestedEndDate == null) ? $requestedDate : $requestedEndDate->ToTimezone($timezone);
		
		$layout = $this->scheduleRepository->GetLayout($requestedScheduleId, new ReservationLayoutFactory($timezone));
		$schedulePeriods = $layout->GetLayout($requestedDate);
		$this->basePage->BindPeriods($schedulePeriods);

		$scheduleUser = $this->scheduleUserRepository->GetUser($userId);

		$this->basePage->SetCanChangeUser($currentUser->IsAdmin || $scheduleUser->IsGroupAdmin());
		
		$bindableResourceData = $this->GetBindableResourceData($scheduleUser, $requestedResourceId);
		$bindableUserData = $this->GetBindableUserData($userId);
		$reservationUser = $bindableUserData->ReservationUser;
		$this->basePage->SetReservationUser($reservationUser);
		
		$this->basePage->BindAvailableResources($bindableResourceData->AvailableResources);		
		
		$this->SetSelectedDates($startDate, $endDate, $schedulePeriods);
		
		$this->basePage->SetReservationResource($bindableResourceData->ReservationResource);
		$this->basePage->SetScheduleId($requestedScheduleId);
	}

	/**
	 * @abstract
	 * @return int
	 */
	protected abstract function GetResourceId();

	/**
	 * @abstract
	 * @return int
	 */
	protected abstract function GetScheduleId();
	
	/**
	 * @return Date
	 */
	protected abstract function GetStartDate();
	
	/**
	 * @return Date
	 */
	protected abstract function GetEndDate();
	
	/**
	 * @return Date
	 */
	protected abstract function GetReservationDate();

	/**
	 * @abstract
	 * @return int
	 */
	protected abstract function GetOwnerId();

	/**
	 * @abstract
	 * @return string
	 */
	protected abstract function GetTimezone();
	
	protected function SetSelectedDates(Date $startDate, Date $endDate, $schedulePeriods)
	{
		$startPeriod = $this->GetStartSlotClosestTo($schedulePeriods, $startDate);
		$endPeriod = $this->GetEndSlotClosestTo($schedulePeriods, $endDate);

		if ($endPeriod->Compare($startPeriod) < 0)
		{
			$endPeriod = $startPeriod;
		}

		$this->basePage->SetSelectedStart($startPeriod, $startDate);
		$this->basePage->SetSelectedEnd($endPeriod, $endDate);
	}

	/**
	 * @param SchedulePeriod[] $periods
	 * @param Date $date
	 * @return SchedulePeriod
	 */
	private function GetStartSlotClosestTo($periods, $date)
	{
		for ($i = 0; $i < count($periods); $i++)
		{
			$currentPeriod = $periods[$i];
			$periodBegin = $currentPeriod->BeginDate();

			if ($currentPeriod->IsReservable() && $periodBegin->CompareTime($date) >= 0)
			{
				return $currentPeriod;
			}
		}

		$lastIndex = count($periods) - 1;
		return $periods[$lastIndex];
	}

	/**
	 * @param SchedulePeriod[] $periods
	 * @param Date $date
	 * @return SchedulePeriod
	 */
	private function GetEndSlotClosestTo($periods, $date)
	{
		for ($i = 0; $i < count($periods); $i++)
		{
			$currentPeriod = $periods[$i];
			$periodEnd = $currentPeriod->EndDate();

			if ($currentPeriod->IsReservable() && $periodEnd->CompareTime($date) >= 0)
			{
				return $currentPeriod;
			}
		}

		$lastIndex = count($periods) - 1;
		return $periods[$lastIndex];
	}
	
	private function GetBindableUserData($userId)
	{
		$user = $this->userRepository->GetById($userId);

		$bindableUserData = new BindableUserData();
		$bindableUserData->SetReservationUser($user);
		
		return $bindableUserData;
	}
	
	private function GetBindableResourceData(IScheduleUser $scheduleUser, $requestedResourceId)
	{
		$resources = $scheduleUser->GetAllResources();
		
		$bindableResourceData = new BindableResourceData();
		
		foreach ($resources as $resource)
		{
			if ($resource->Id() != $requestedResourceId)
			{
				$bindableResourceData->AddAvailableResource($resource);
			}
			else
			{
				$bindableResourceData->SetReservationResource($resource);
			}
		}
		
		return $bindableResourceData;
	}


}
?>