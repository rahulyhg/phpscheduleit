<?php

class Group
{
	private $id;
	private $name;
	
	private $addedUsers = array();
	private $removedUsers = array();
	private $users = array();
	
	private $permissionsChanged = false;
	private $removedPermissions = array();
	private $addedPermissions = array();
	private $allowedResourceIds = array();

	private $rolesChanged = false;

	/**
	 * @var array|int[]
	 */
	private $removedRoleIds = array();

	/**
	 * @var array|int[]
	 */
	private $addedRoleIds = array();

	/**
	 * @var array|RoleDto[]
	 */
	private $roleIds = array();

	/**
	 * @param $id int
	 * @param $name string
	 */
	public function __construct($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * @return int
	 */
	public function Id()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function Name()
	{
		return $this->name;
	}

	/**
	 * @param $groupName string
	 * @return void
	 */
	public function Rename($groupName)
	{
		$this->name = $groupName;
	}

	/**
	 * @param $userId int
	 * @return void
	 */
	public function AddUser($userId)
	{
		if (!$this->HasMember($userId))
		{
			$this->addedUsers[] = $userId;
		}
	}

	/**
	 * @param $userId int
	 * @return void
	 */
	public function RemoveUser($userId)
	{
		if ($this->HasMember($userId))
		{
			$this->removedUsers[] = $userId;
		}
	}

	/**
	 * @internal
	 * @return int[] array of userIds
	 */
	public function AddedUsers()
	{
		return $this->addedUsers;
	}
	
	/**
	 * @internal
	 * @return int[] array of userIds
	 */
	public function RemovedUsers()
	{
		return $this->removedUsers;
	}

	/**
	 * @internal
	 * @return array|int[]
	 */
	public function AddedRoles()
	{
		return $this->addedRoleIds;
	}

	/**
	 * @internal
	 * @return array|int[]
	 */
	public function RemovedRoles()
	{
		return $this->removedRoleIds;
	}

	/**
	 * @internal
	 * @param $groupId
	 * @return void
	 */
	public function WithId($groupId)
	{
		$this->id = $groupId;
	}

	/**
	 * @internal
	 * @param $userId
	 * @return void
	 */
	public function WithUser($userId)
	{
		$this->users[] = $userId;
	}

	/**
	 * @param $userId
	 * @return bool
	 */
	public function HasMember($userId)
	{
		return in_array($userId, $this->users);
	}
	
	/**
	 * @param int $allowedResourceId
	 * @return void
	 */
	public function WithPermission($allowedResourceId)
	{
		$this->permissionsChanged = false;
		$this->allowedResourceIds[] = $allowedResourceId;
	}

	/**
	 * @param $role int
	 * @return void
	 */
	public function WithRole($role)
	{
		$this->rolesChanged = false;
		$this->roleIds[] = $role;
	}

	/**
	 * @param int[] $allowedResourceIds
	 * @return void
	 */
	public function ChangePermissions($allowedResourceIds = array())
	{
		$diff = new ArrayDiff($this->allowedResourceIds, $allowedResourceIds);
		$removed = $diff->GetRemovedFromArray1();
		$added = $diff->GetAddedToArray1();

		if ($diff->AreDifferent())
		{
			$this->permissionsChanged = true;
			$this->removedPermissions = $removed;
			$this->addedPermissions = $added;

			$this->allowedResourceIds = $allowedResourceIds;
		}
	}

	/**
	 * @internal
	 * @return int[]|array of resourceIds
	 */
	public function RemovedPermissions()
	{
		return $this->removedPermissions;
	}

	/**
	 * @internal
	 * @return int[]|array of resourceIds
	 */
	public function AddedPermissions()
	{
		return $this->addedPermissions;
	}

	/**
	 * @return array|int[]
	 */
	public function AllowedResourceIds()
	{
		return $this->allowedResourceIds;
	}

	/**
	 * @return array|int[]
	 */
	public function RoleIds()
	{
		return $this->roleIds;
	}

	/**
	 * @param $roleIds int[]|array
	 * @return void
	 */
	public function ChangeRoles($roleIds)
	{
		$diff = new ArrayDiff($this->roleIds, $roleIds);
		$removed = $diff->GetRemovedFromArray1();
		$added = $diff->GetAddedToArray1();

		if ($diff->AreDifferent())
		{
			$this->rolesChanged = true;
			$this->removedRoleIds = $removed;
			$this->addedRoleIds = $added;

			$this->roleIds = $roleIds;
		}
	}
}

?>