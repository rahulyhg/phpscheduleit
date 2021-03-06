<?php
/**
Copyright 2011-2014 Nick Korbel

This file is part of Booked Scheduler.

Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Booked Scheduler is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'Domain/ScheduleLayout.php');
require_once(ROOT_DIR . 'Domain/Schedule.php');
require_once(ROOT_DIR . 'Domain/SchedulePeriod.php');
require_once(ROOT_DIR . 'lib/Database/Commands/namespace.php');

interface IScheduleRepository
{
    /**
     * Gets all schedules
     * @return array|Schedule[]
     */
    public function GetAll();

    /**
     * @param int $scheduleId
     * @return Schedule
     */
    public function LoadById($scheduleId);

    /**
     * @abstract
     * @param string $publicId
     * @return Schedule
     */
    public function LoadByPublicId($publicId);

    /**
     * @param Schedule $schedule
     */
    public function Update(Schedule $schedule);

    /**
     * @param Schedule $schedule
     */
    public function Delete(Schedule $schedule);

    /**
     * @param Schedule $schedule
     * @param int $copyLayoutFromScheduleId
     * @return int $insertedScheduleId
     */
    public function Add(Schedule $schedule, $copyLayoutFromScheduleId);

    /**
     * @param int $scheduleId
     * @param ILayoutFactory $layoutFactory factory to use to create the schedule layout
     * @return IScheduleLayout
     */
    public function GetLayout($scheduleId, ILayoutFactory $layoutFactory);

    /**
     * @param int $scheduleId
     * @param ILayoutCreation $layout
     */
    public function AddScheduleLayout($scheduleId, ILayoutCreation $layout);
}

interface ILayoutFactory
{
    /**
     * @return IScheduleLayout
     */
    public function CreateLayout();
}

class ScheduleLayoutFactory implements ILayoutFactory
{
    private $_targetTimezone;

    /**
     * @param string $targetTimezone target timezone of layout
     */
    public function __construct($targetTimezone)
    {
        $this->_targetTimezone = $targetTimezone;
    }

    /**
     * @see ILayoutFactory::CreateLayout()
     */
    public function CreateLayout()
    {
        return new ScheduleLayout($this->_targetTimezone);
    }
}

class ReservationLayoutFactory implements ILayoutFactory
{
    private $_targetTimezone;

    /**
     * @param string $targetTimezone target timezone of layout
     */
    public function __construct($targetTimezone)
    {
        $this->_targetTimezone = $targetTimezone;
    }

    /**
     * @see ILayoutFactory::CreateLayout()
     */
    public function CreateLayout()
    {
        return new ReservationLayout($this->_targetTimezone);
    }
}

class ScheduleRepository implements IScheduleRepository
{

    public function __construct()
    {
    }

    public function GetAll()
    {
        $schedules = array();

        $reader = ServiceLocator::GetDatabase()->Query(new GetAllSchedulesCommand());

        while ($row = $reader->GetRow())
        {
            $schedules[] = Schedule::FromRow($row);
        }

        $reader->Free();

        return $schedules;
    }

    public function LoadById($scheduleId)
    {
        if (!DomainCache::Exists($scheduleId, 'schedule'))
        {
            $schedule = null;

            $reader = ServiceLocator::GetDatabase()->Query(new GetScheduleByIdCommand($scheduleId));

            if ($row = $reader->GetRow())
            {
                $schedule = Schedule::FromRow($row);
            }

			DomainCache::Add($scheduleId, $schedule, 'schedule');
            $reader->Free();

        }

        return DomainCache::Get($scheduleId, 'schedule');
    }

    public function LoadByPublicId($publicId)
    {
        $schedule = Schedule::Null();

        $reader = ServiceLocator::GetDatabase()->Query(new GetScheduleByPublicIdCommand($publicId));

        if ($row = $reader->GetRow())
        {
            $schedule = Schedule::FromRow($row);
        }

        $reader->Free();

        return $schedule;
    }

    public function Update(Schedule $schedule)
    {
        ServiceLocator::GetDatabase()->Execute(new UpdateScheduleCommand(
                                                   $schedule->GetId(),
                                                   $schedule->GetName(),
                                                   $schedule->GetIsDefault(),
                                                   $schedule->GetWeekdayStart(),
                                                   $schedule->GetDaysVisible(),
                                                   $schedule->GetIsCalendarSubscriptionAllowed(),
                                                   $schedule->GetPublicId(),
											  	   $schedule->GetAdminGroupId()));

        if ($schedule->GetIsDefault())
        {
            ServiceLocator::GetDatabase()->Execute(new SetDefaultScheduleCommand($schedule->GetId()));
        }

		DomainCache::Add($schedule->GetId(), $schedule, 'schedule');
    }

    public function Add(Schedule $schedule, $copyLayoutFromScheduleId)
    {
        $source = $this->LoadById($copyLayoutFromScheduleId);

        $db = ServiceLocator::GetDatabase();

        return $db->ExecuteInsert(new AddScheduleCommand(
                                      $schedule->GetName(),
                                      $schedule->GetIsDefault(),
                                      $schedule->GetWeekdayStart(),
                                      $schedule->GetDaysVisible(),
                                      $source->GetLayoutId(),
									  $schedule->GetAdminGroupId()
                                  ));
    }

    public function Delete(Schedule $schedule)
    {
        ServiceLocator::GetDatabase()->Execute(new DeleteScheduleCommand($schedule->GetId()));
		DomainCache::Remove($schedule->GetId(), 'schedule');
    }

    public function GetLayout($scheduleId, ILayoutFactory $layoutFactory)
    {
		if (!DomainCache::Exists($scheduleId, 'layout'))
		{
			/**
			 * @var $layout ScheduleLayout
			 */
			$layout = $layoutFactory->CreateLayout();

			$reader = ServiceLocator::GetDatabase()->Query(new GetLayoutCommand($scheduleId));

			while ($row = $reader->GetRow())
			{
				$timezone = $row[ColumnNames::BLOCK_TIMEZONE];
				$start = Time::Parse($row[ColumnNames::BLOCK_START], $timezone);
				$end = Time::Parse($row[ColumnNames::BLOCK_END], $timezone);
				$label = $row[ColumnNames::BLOCK_LABEL];
				$periodType = $row[ColumnNames::BLOCK_CODE];
				$dayOfWeek = $row[ColumnNames::BLOCK_DAY_OF_WEEK];

				if ($periodType == PeriodTypes::RESERVABLE)
				{
					$layout->AppendPeriod($start, $end, $label, $dayOfWeek);
				}
				else
				{
					$layout->AppendBlockedPeriod($start, $end, $label, $dayOfWeek);
				}
			}

			DomainCache::Add($scheduleId, $layout, 'layout');

			$reader->Free();
		}

		return DomainCache::Get($scheduleId, 'layout');
    }

    public function AddScheduleLayout($scheduleId, ILayoutCreation $layout)
    {
        $db = ServiceLocator::GetDatabase();
        $timezone = $layout->Timezone();

        $addLayoutCommand = new AddLayoutCommand($timezone);
        $layoutId = $db->ExecuteInsert($addLayoutCommand);

		$days = array(null);
		if ($layout->UsesDailyLayouts())
		{
			$days = DayOfWeek::Days();
		}

		foreach ($days as $day)
		{
			$slots = $layout->GetSlots($day);

			/* @var $slot LayoutPeriod */
			foreach ($slots as $slot)
			{
				$db->Execute(new AddLayoutTimeCommand($layoutId, $slot->Start, $slot->End, $slot->PeriodType, $slot->Label, $day));
			}
		}
        $db->Execute(new UpdateScheduleLayoutCommand($scheduleId, $layoutId));

        $db->Execute(new DeleteOrphanLayoutsCommand());
    }
}