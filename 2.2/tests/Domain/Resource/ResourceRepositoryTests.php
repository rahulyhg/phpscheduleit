<?php
/**
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
 */

require_once(ROOT_DIR . 'lib/Common/namespace.php');
require_once(ROOT_DIR . 'Domain/namespace.php');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'tests/fakes/namespace.php');

class ResourceRepositoryTests extends TestBase
{
    public function setup()
    {
        parent::setup();
    }

    public function teardown()
    {
        parent::teardown();
    }

    public function testCanGetAllResourcesForASchedule()
    {
        $expected = array();
        $scheduleId = 10;

        $ra = new FakeResourceAccess();
        $rows = $ra->GetRows();
        $this->db->SetRow(0, $rows);

        foreach ($rows as $row)
        {
            $expected[] = BookableResource::Create($row);
        }

        $resourceAccess = new ResourceRepository();
        $resources = $resourceAccess->GetScheduleResources($scheduleId);

        $this->assertEquals(new GetScheduleResourcesCommand($scheduleId), $this->db->_Commands[0]);
        $this->assertTrue($this->db->GetReader(0)->_FreeCalled);
        $this->assertEquals(count($rows), count($resources));
        $this->assertEquals($expected, $resources);
    }

    public function testCanUpdateResource()
    {
        $id = 8383;
        $name = "name";
        $location = "location";
        $contact = "contact";
        $notes = "notes";
        $minLength = 720;
        $maxLength = 727272;
        $autoAssign = 1;
        $requiresApproval = 0;
        $allowMultiday = 1;
        $maxParticipants = 100;
        $minNotice = 11111;
        $maxNotice = 22222;
        $description = "description";
        $scheduleId = 19819;
        $imageName = 'something.png';
        $adminGroupId = 232;
        $allowSubscription = true;

        $resource = new BookableResource($id,
            $name,
            $location,
            $contact,
            $notes,
            $minLength,
            $maxLength,
            $autoAssign,
            $requiresApproval,
            $allowMultiday,
            $maxParticipants,
            $minNotice,
            $maxNotice,
            $description,
            $scheduleId);
        $resource->SetImage($imageName);
        $resource->BringOnline();
        $resource->SetAdminGroupId($adminGroupId);
        $resource->EnableSubscription();

        $publicId = $resource->GetPublicId();

        $resourceRepository = new ResourceRepository();
        $resourceRepository->Update($resource);

        $expectedUpdateResourceCommand = new UpdateResourceCommand(
            $id,
            $name,
            $location,
            $contact,
            $notes,
            new TimeInterval($minLength),
            new TimeInterval($maxLength),
            $autoAssign,
            $requiresApproval,
            $allowMultiday,
            $maxParticipants,
            new TimeInterval($minNotice),
            new TimeInterval($maxNotice),
            $description,
            $imageName,
            $resource->IsOnline(),
            $scheduleId,
            $adminGroupId,
            $allowSubscription,
            $publicId);

        $actualUpdateResourceCommand = $this->db->_Commands[0];

        $this->assertEquals($expectedUpdateResourceCommand, $actualUpdateResourceCommand);
    }

    public function testCanAddResourceWithMinimumAttributes()
    {
        $name = "name";
        $scheduleId = 828;
        $resourceId = 8888;
        $autoAssign = true;
        $groupId = 111;

        $resource = BookableResource::CreateNew($name, $scheduleId, $autoAssign);
        $resource->SetAdminGroupId($groupId);

        $this->db->_ExpectedInsertId = $resourceId;

        $resourceRepository = new ResourceRepository();
        $resourceRepository->Add($resource);

        $expectedAddCommand = new AddResourceCommand($name, $scheduleId, $autoAssign, $groupId);
        $assignResourcePermissions = new AutoAssignResourcePermissionsCommand($resourceId);
        $actualAddResourceCommand = $this->db->_Commands[0];
        $actualAssignResourcePermissions = $this->db->_Commands[1];

        $this->assertEquals($expectedAddCommand, $actualAddResourceCommand);
        $this->assertEquals($assignResourcePermissions, $actualAssignResourcePermissions);
    }

    public function testDeletingAResourceRemovesAllAssociatedData()
    {
        $resourceId = 100;
        $resource = BookableResource::CreateNew('name', 1);
        $resource->SetResourceId($resourceId);

        $resourceRepository = new ResourceRepository();
        $resourceRepository->Delete($resource);

        $deleteReservations = new DeleteResourceReservationsCommand($resourceId);
        $deleteResources = new DeleteResourceCommand($resourceId);

        $actualDeleteReservations = $this->db->_Commands[0];
        $actualDeleteResources = $this->db->_Commands[1];

        $this->assertEquals($deleteReservations, $actualDeleteReservations);
        $this->assertEquals($deleteResources, $actualDeleteResources);
    }

    public function testGetsAccessories()
    {
		$ar = new ReservationAccessoryRow();
		$ar->WithAccessory(1, 3, "name")
				->WithAccessory(2, 23, "slkjdf");

        $this->db->SetRows($ar->Rows());

        $getAccessoriesCommand = new GetAllAccessoriesCommand();

        $resourceRepository = new ResourceRepository();
        /** @var $accessories AccessoryDto[] */
        $accessories = $resourceRepository->GetAccessoryList();

        $this->assertEquals($getAccessoriesCommand, $this->db->_LastCommand);
        $this->assertEquals(2, count($accessories));
        $this->assertEquals(1, $accessories[0]->Id);
        $this->assertEquals("name", $accessories[0]->Name);
        $this->assertEquals(3, $accessories[0]->QuantityAvailable);
    }

    public function testLoadsResourceByPublicId()
    {
        $publicId = uniqid();

        $fr = new FakeResourceAccess();
        $rows = $fr->GetRows();
        $this->db->SetRow(0, $rows);

		$car = new CustomAttributeValueRow();
		$car->With(1, 'value')
			->With(2, 'value2');
        $this->db->SetRow(1, $car->Rows());
        $loadResourceCommand = new GetResourceByPublicIdCommand($publicId);
        $attributes = new GetAttributeValuesCommand(1, CustomAttributeCategory::RESOURCE);

        $resourceRepository = new ResourceRepository();
        $resource = $resourceRepository->LoadByPublicId($publicId);

        $this->assertTrue($this->db->ContainsCommand($loadResourceCommand));
        $this->assertTrue($this->db->ContainsCommand($attributes));
        $this->assertNotNull($resource);
        $this->assertEquals('value', $resource->GetAttributeValue(1));
        $this->assertEquals('value2', $resource->GetAttributeValue(2));
    }

	public function testLoadsResourceById()
	{
		$id = 1;

		$fr = new FakeResourceAccess();
		$rows = $fr->GetRows();
		$this->db->SetRow(0, $rows);

		$car = new CustomAttributeValueRow();
		$car->With(1, 'value')
			->With(2, 'value2');
		$this->db->SetRow(1, $car->Rows());
		$loadResourceCommand = new GetResourceByIdCommand($id);
		$attributes = new GetAttributeValuesCommand(1, CustomAttributeCategory::RESOURCE);

		$resourceRepository = new ResourceRepository();
		$resource = $resourceRepository->LoadById($id);

		$this->assertTrue($this->db->ContainsCommand($loadResourceCommand));
		$this->assertTrue($this->db->ContainsCommand($attributes));
		$this->assertNotNull($resource);
		$this->assertEquals('value', $resource->GetAttributeValue(1));
		$this->assertEquals('value2', $resource->GetAttributeValue(2));
	}

	public function testUpdatesAttributes()
	{
		$id = 11;
		$unchanged = new AttributeValue(1, 'value');
		$toChange = new AttributeValue(2, 'value');
		$toAdd = new AttributeValue(3, 'value');

		$resource = new FakeBookableResource($id);
		$resource->WithAttribute($unchanged);
		$resource->WithAttribute(new AttributeValue(100, 'should be removed'));
		$resource->WithAttribute(new AttributeValue(2, 'new value'));

		$attributes = array($unchanged, $toChange, $toAdd);
		$resource->ChangeAttributes($attributes);

		$resourceRepository = new ResourceRepository();
		$resourceRepository->Update($resource);

		$addNewCommand = new AddAttributeValueCommand($toAdd->AttributeId, $toAdd->Value, $id, CustomAttributeCategory::RESOURCE);
		$removeOldCommand = new RemoveAttributeValueCommand(100, $id);
		$removeUpdated = new RemoveAttributeValueCommand($toChange->AttributeId, $id);
		$addUpdated = new AddAttributeValueCommand($toChange->AttributeId, $toChange->Value, $id, CustomAttributeCategory::RESOURCE);

		$this->assertEquals($removeOldCommand, $this->db->_Commands[1]);
		$this->assertEquals($removeUpdated, $this->db->_Commands[2], "need to remove before adding to make sure changed values are not immediately deleted");
		$this->assertEquals($addUpdated, $this->db->_Commands[3]);
		$this->assertEquals($addNewCommand, $this->db->_Commands[4]);
	}
}

?>