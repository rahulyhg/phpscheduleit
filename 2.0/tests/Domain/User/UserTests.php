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

require_once(ROOT_DIR . 'Domain/namespace.php');

class UserTests extends TestBase
{
	public function testUserIsGroupAdminIfAtLeastOneGroupIsAnAdminGroup()
	{
		$user = new User();

		$nonAdminGroup = new UserGroup(1, 'non admin', 2, RoleLevel::NONE);
		$adminGroup = new UserGroup(2, 'admin', null, RoleLevel::GROUP_ADMIN);
		$groups = array($nonAdminGroup, $adminGroup);

		$user->WithGroups($groups);

		$this->assertTrue($user->IsGroupAdmin());
	}
	
	public function testWhenUserIsInAGroupThatCanAdminAnotherGroup()
	{
		$adminGroupId = 99;
		$groupId1 = 1;
		$groupId2 = 2;
		
		$adminUser = new User();
		$user = new User();

		$adminGroup = new UserGroup($adminGroupId, 'admin', null, RoleLevel::NONE);
		$adminGroup->AddRole(RoleLevel::GROUP_ADMIN);
		$group1 = new UserGroup($groupId1, 'random group');
		$group2 = new UserGroup($groupId2, 'group with admin', $adminGroupId, RoleLevel::NONE);
		
		$adminUserGroups = array($group1, $adminGroup);
		$userGroups = array($group2);
		
		$adminUser->WithGroups($adminUserGroups);
		$user->WithGroups($userGroups);

		$this->assertTrue($adminUser->IsAdminFor($user), 'admin of group 2');
	}

	public function testWhenUserIsNotInAGroupThatCanAdminAnotherGroup()
	{
		$adminGroupId = 99;
		$groupId1 = 1;
		$groupId2 = 2;

		$adminUser = new User();
		$user = new User();

		$adminGroup = new UserGroup($adminGroupId, 'admin', null, RoleLevel::GROUP_ADMIN);
		$group1 = new UserGroup($groupId1, 'random group');
		$group2 = new UserGroup($groupId2, 'group with admin', $groupId1, RoleLevel::NONE);

		$adminUserGroups = array($group1, $adminGroup);
		$userGroups = array($group1, $group2);

		$adminUser->WithGroups($adminUserGroups);
		$user->WithGroups($userGroups);

		$this->assertFalse($adminUser->IsAdminFor($user), 'admin is not in any group that can admin group 1 or 2');
	}
}