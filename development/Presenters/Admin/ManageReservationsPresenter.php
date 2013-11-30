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

require_once(ROOT_DIR . 'Pages/Admin/ManageReservationsPage.php');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'Presenters/ActionPresenter.php');
require_once(ROOT_DIR . 'lib/Application/Admin/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Attributes/namespace.php');

class ManageReservationsPresenter extends ActionPresenter
{
	/**
	 * @var IManageReservationsPage
	 */
	private $page;

	/**
	 * @var IManageReservationsService
	 */
	private $manageReservationsService;

	/**
	 * @var IScheduleRepository
	 */
	private $scheduleRepository;

	/**
	 * @var IResourceRepository
	 */
	private $resourceRepository;

	/**
	 * @var IAttributeService
	 */
	private $attributeService;

	public function __construct(
		IManageReservationsPage $page,
		IManageReservationsService $manageReservationsService,
		IScheduleRepository $scheduleRepository,
		IResourceRepository $resourceRepository,
		IAttributeService $attributeService)
	{
		parent::__construct($page);

		$this->page = $page;
		$this->manageReservationsService = $manageReservationsService;
		$this->scheduleRepository = $scheduleRepository;
		$this->resourceRepository = $resourceRepository;
		$this->attributeService = $attributeService;
	}

	public function PageLoad($userTimezone)
	{
		$session = ServiceLocator::GetServer()->GetUserSession();

		$this->page->BindSchedules($this->scheduleRepository->GetAll());
		$this->page->BindResources($this->resourceRepository->GetResourceList());

		$startDateString = $this->page->GetStartDate();
		$endDateString = $this->page->GetEndDate();

		$startDate = $this->GetDate($startDateString, $userTimezone, $session->GetFilterStartDateDelta());
		$endDate   = $this->GetDate($endDateString  , $userTimezone, $session->GetFilterEndDateDelta());
		if(!$this->page->FilterButtonPressed())
		{
			// Get filter settings from db
			$reservationStatusId = $session->GetFilterReservationStatusId();
			$referenceNumber = $session->GetFilterReferenceNumber();
			$scheduleId = $session->GetFilterScheduleId();
			$userId = $session->GetFilterUserId();
			$resourceId = $session->GetFilterResourceId();
		}
		else
		{
			// Get filter settings from page and save them in db
			$session->SetFilterStartDateDelta($this->GetDateOffsetFromToday($startDate, $userTimezone));
			$session->SetFilterEndDateDelta($this->GetDateOffsetFromToday($endDate, $userTimezone));
			$session->SetFilterReferenceNumber($referenceNumber = $this->page->GetReferenceNumber());
			$session->SetFilterScheduleId($scheduleId = $this->page->GetScheduleId());
			$session->SetFilterResourceId($resourceId = $this->page->GetResourceId());
			$session->SetFilterUserId($userId = $this->page->GetUserId());
			$session->SetFilterReservationStatusId($reservationStatusId = $this->page->GetReservationStatusId());
		}
		$userName = $this->page->GetUserName();

		$this->page->SetStartDate($startDate);
		$this->page->SetEndDate($endDate);
		$this->page->SetReferenceNumber($referenceNumber);
		$this->page->SetScheduleId($scheduleId);
		$this->page->SetResourceId($resourceId);
		$this->page->SetUserId($userId);
		$this->page->SetUserName($userName);
		$this->page->SetReservationStatusId($reservationStatusId);

		$filter = new ReservationFilter($startDate, $endDate, $referenceNumber, $scheduleId, $resourceId, $userId, $reservationStatusId);

		$reservations = $this->manageReservationsService->LoadFiltered($this->page->GetPageNumber(),
																	   $this->page->GetPageSize(),
																	   $filter,
																	   $session);

		$reservationList = $reservations->Results();
		$this->page->BindReservations($reservationList);
		$this->page->BindPageInfo($reservations->PageInfo());

		$seriesIds = array();
		/** @var $reservationItemView ReservationItemView */
		foreach ($reservationList as $reservationItemView)
		{
			$seriesIds[] = $reservationItemView->SeriesId;
		}

		$attributeList = $this->attributeService->GetAttributes(CustomAttributeCategory::RESERVATION, $seriesIds);
		$this->page->SetAttributes($attributeList);

		if ($this->page->GetFormat() == 'csv')
		{
			$this->page->ShowCsv();
		}
		else
		{
			$this->page->ShowPage();
		}
	}

	private function GetDate($dateString, $timezone, $defaultDays)
	{
		$date = null;
		if (is_null($dateString)) {
			$date = Date::Now()->AddDays($defaultDays)->ToTimezone($timezone)->GetDate();

		}
		elseif (!empty($dateString))
		{
			$date = Date::Parse($dateString, $timezone);
		}

		return $date;
	}

	private function GetDateOffsetFromToday($date, $timezone)
	{
		//$diff = DateDiff::BetweenDates(Date::Now(), $date);
		$today = Date::Create(Date('Y'), Date('m'), Date('d'), 0, 0, 0, $timezone);
		$diff = DateDiff::BetweenDates($today, $date);
		return $diff->Days();
	}
}

?>