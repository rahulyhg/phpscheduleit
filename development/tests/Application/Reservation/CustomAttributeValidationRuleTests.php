<?php
/**
 * Copyright 2011-2014 Nick Korbel
 *
 * This file is part of Booked Scheduler.
 *
 * Booked Scheduler is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Booked Scheduler is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'Domain/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Reservation/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Reservation/Validation/namespace.php');

class CustomAttributeValidationRuleTests extends TestBase
{
	/**
	 * @var IAttributeService|PHPUnit_Framework_MockObject_MockObject
	 */
	private $attributeService;

	/**
	 * @var IUserRepository|PHPUnit_Framework_MockObject_MockObject
	 */
	private $userRepository;

	/**
	 * @var CustomAttributeValidationRule
	 */
	private $rule;

	/**
	 * @var TestReservationSeries
	 */
	private $reservation;

	/**
	 * @var FakeUser
	 */
	private $user;

	/**
	 * @var FakeUser
	 */
	private $bookedBy;

	public function setup()
	{
		parent::setup();

		$this->attributeService = $this->getMock('IAttributeService');
		$this->userRepository = $this->getMock('IUserRepository');

		$this->reservation = new TestReservationSeries();
		$this->user = new FakeUser(1);
		$this->bookedBy = new FakeUser(2);

		$this->userRepository->expects($this->at(0))
							 ->method('LoadById')
							 ->with($this->equalTo($this->reservation->UserId()))
							 ->will($this->returnValue($this->user));

		$this->userRepository->expects($this->at(1))
							 ->method('LoadById')
							 ->with($this->equalTo($this->reservation->BookedBy()->UserId))
							 ->will($this->returnValue($this->bookedBy));

		$this->rule = $rule = new CustomAttributeValidationRule($this->attributeService, $this->userRepository);
	}

	public function teardown()
	{
		parent::teardown();
	}

	public function testChecksEachAttributeInCategory()
	{
		$errors = array('error1', 'error2');

		$validationResult = new AttributeServiceValidationResult(false, $errors);
		$this->attributeService->expects($this->once())
				->method('Validate')
				->with($this->equalTo(CustomAttributeCategory::RESERVATION), $this->equalTo($this->reservation->AttributeValues()), $this->isNull(), $this->isFalse(), $this->isFalse())
				->will($this->returnValue($validationResult));

		$userAttribute = new FakeCustomAttribute();
		$userAttribute->WithSecondaryEntity(CustomAttributeCategory::USER, 123);

		$result = $this->rule->Validate($this->reservation);

		$this->assertEquals(false, $result->IsValid());
		$this->assertContains('error1', $result->ErrorMessage());
		$this->assertContains('error2', $result->ErrorMessage());
		$this->assertNotContains('error3', $result->ErrorMessage(), "don't include the 3rd error because it's for an attribute that doesn't apply");
	}

	public function testWhenAllAttributesAreValid()
	{
		$validationResult = new AttributeServiceValidationResult(true, array());

		$this->attributeService->expects($this->once())
				->method('Validate')
				->with($this->equalTo(CustomAttributeCategory::RESERVATION), $this->equalTo($this->reservation->AttributeValues()), $this->isNull(), $this->isFalse(), $this->isFalse())
				->will($this->returnValue($validationResult));

		$result = $this->rule->Validate($this->reservation);

		$this->assertEquals(true, $result->IsValid());
	}

	public function testWhenUserIsAnAdmin_ThenEvaluateAdminOnlyAttributes()
	{
		$this->bookedBy->_IsAdminForUser = true;
		$validationResult = new AttributeServiceValidationResult(true, array());

		$this->attributeService->expects($this->once())
				->method('Validate')
				->with($this->equalTo(CustomAttributeCategory::RESERVATION), $this->equalTo($this->reservation->AttributeValues()), $this->isNull(), $this->isFalse(), $this->isTrue())
				->will($this->returnValue($validationResult));

		$result = $this->rule->Validate($this->reservation);

		$this->assertEquals(true, $result->IsValid());
	}

	public function testWhenTheInvalidAttributeIsForASecondaryUser_AndTheReservationUserIsNotThatUser()
	{
		$reservation = new TestReservationSeries();
		$reservation->WithOwnerId(999);
		$reservation->WithAttributeValue(new AttributeValue(1, null));

		$attributeService = $this->getMock('IAttributeService');

		$userAttribute = new FakeCustomAttribute();
		$userAttribute->WithSecondaryEntity(CustomAttributeCategory::USER, 123);

		$validationResult = new AttributeServiceValidationResult(false, array('error'), array(new InvalidAttribute($userAttribute, 'another message')));

		$attributeService->expects($this->once())
				->method('Validate')
				->with($this->equalTo(CustomAttributeCategory::RESERVATION), $this->equalTo($reservation->AttributeValues()))
				->will($this->returnValue($validationResult));

		$rule = new CustomAttributeValidationRule($attributeService, $this->userRepository);

		$result = $rule->Validate($reservation);

		$this->assertEquals(true, $result->IsValid());
		$this->assertEmpty($result->ErrorMessage());
	}
}