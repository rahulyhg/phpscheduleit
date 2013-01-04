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

require_once(ROOT_DIR . 'Domain/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');

class ResourceMaximumNoticeRuleTests extends TestBase
{
	public function setup()
	{
		parent::setup();
	}

	public function teardown()
	{
		parent::teardown();
	}
	
	public function testMaxNoticeIsCheckedAgainstEachReservationInstanceForEachResource()
	{
		$resource1 = new FakeBookableResource(1, "1");
		$resource1->SetMaxNotice(null);
		
		$resource2 = new FakeBookableResource(2, "2");
		$resource2->SetMaxNotice("23h00m");
		
		$reservation = new TestReservationSeries();
		
		$duration = new DateRange(Date::Now(), Date::Now());
		$tooFarInFuture = Date::Now()->AddDays(1);
		$reservation->WithDuration($duration);
		$reservation->WithRepeatOptions(new RepeatDaily(1, $tooFarInFuture));
		$reservation->WithResource($resource1);
		$reservation->AddResource($resource2);
			
		$rule = new ResourceMaximumNoticeRule();
		$result = $rule->Validate($reservation);
		
		$this->assertFalse($result->IsValid());
	}
	
	public function testOkIfLatestInstanceIsBeforeTheMaximumNoticeTime()
	{
		$resource = new FakeBookableResource(1, "2");
		$resource->SetMaxNotice("1h00m");
			
		$reservation = new TestReservationSeries();
		$reservation->WithResource($resource);
		
		$duration = new DateRange(Date::Now(), Date::Now());
		$reservation->WithDuration($duration);
		
		$rule = new ResourceMaximumNoticeRule();
		$result = $rule->Validate($reservation);
		
		$this->assertTrue($result->IsValid());
	}
}
?>