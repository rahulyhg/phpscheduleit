<?php
require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'Presenters/ActionPresenter.php');
require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');

class ManageGroupsActions
{
	const Activate = 'activate';
	const Deactivate = 'deactivate';
	const Password = 'password';
	const Permissions = 'permissions';
	const RemoveUser = 'removeUser';
	const AddUser = 'addUser';
	const AddGroup = 'addGroup';
	const RenameGroup = 'renameGroup';
	const DeleteGroup = 'deleteGroup';
}

class ManageGroupsPresenter extends ActionPresenter
{
	/**
	 * @var IManageGroupsPage
	 */
	private $page;

	/**
	 * @var GroupRepository
	 */
	private $groupRepository;

	/**
	 * @var ResourceRepository
	 */
	private $resourceRepository;

	/**
	 * @param IManageGroupsPage $page
	 * @param GroupRepository $groupRepository
	 * @param ResourceRepository $resourceRepository
	 */
	public function __construct(IManageGroupsPage $page, GroupRepository $groupRepository, ResourceRepository $resourceRepository)
	{
		parent::__construct($page);

		$this->page = $page;
		$this->groupRepository = $groupRepository;
		$this->resourceRepository = $resourceRepository;

		$this->AddAction(ManageGroupsActions::AddUser, 'AddUser');
		$this->AddAction(ManageGroupsActions::RemoveUser, 'RemoveUser');
		$this->AddAction(ManageGroupsActions::Permissions, 'ChangePermissions');
		$this->AddAction(ManageGroupsActions::AddGroup, 'AddGroup');
		$this->AddAction(ManageGroupsActions::RenameGroup, 'RenameGroup');
		$this->AddAction(ManageGroupsActions::DeleteGroup, 'DeleteGroup');
	}

	public function PageLoad()
	{
		if ($this->page->GetGroupId() != null)
		{
			$groupList = $this->groupRepository->GetList(1, 1, null, null, new SqlFilterEquals(ColumnNames::GROUP_ID, $this->page->GeTGroupId()));
		}
		else
		{
			$groupList = $this->groupRepository->GetList($this->page->GetPageNumber(), $this->page->GetPageSize());
		}

		$this->page->BindGroups($groupList->Results());
		$this->page->BindPageInfo($groupList->PageInfo());

		$this->page->BindResources($this->resourceRepository->GetResourceList());
		$this->page->BindRoles(array(new RoleDto(1,'Group Admin', RoleLevel::GROUP_ADMIN), new RoleDto(2, 'Application Admin', RoleLevel::APPLICATION_ADMIN)));
	}


	public function ChangePermissions()
	{
		$group = $this->groupRepository->LoadById($this->page->GetGroupId());
		$allowedResources = array();

		if (is_array($this->page->GetAllowedResourceIds())) {
			$allowedResources = $this->page->GetAllowedResourceIds();
		}
		$group->ChangePermissions($allowedResources);
		$this->groupRepository->Update($group);
	}

	public function ProcessDataRequest()
	{
		$response = '';
		$request = $this->page->GetDataRequest();
		switch($request)
		{
			case 'groupMembers' :
				$users = $this->groupRepository->GetUsersInGroup($this->page->GetGroupId(), 1, 100);
				$response = new UserGroupResults($users->Results(), $users->PageInfo()->Total);
				break;
			case 'permissions' :
				$response = $this->GetGroupResourcePermissions();
				break;
			case 'roles' :
				$response = $this->GetGroupRoles();
				break;
		}
	
		$this->page->SetJsonResponse($response);
	}

	/**
	 * @return int[] all resource ids the user has permission to
	 */
	public function GetGroupResourcePermissions()
	{
		$group = $this->groupRepository->LoadById($this->page->GetGroupId());
		return $group->AllowedResourceIds();
	}

	protected function AddUser()
	{
		$groupId = $this->page->GetGroupId();
		$userId = $this->page->GetUserId();

		Log::Debug("Adding userId: %s from groupId: %s", $userId, $groupId);

		$group = $this->groupRepository->LoadById($groupId);
		$group->AddUser($userId);
		$this->groupRepository->Update($group);
	}

	protected function RemoveUser()
	{
		$groupId = $this->page->GetGroupId();
		$userId = $this->page->GetUserId();

		Log::Debug('Removing userId: %s from groupId: %s', $userId, $groupId);

		$group = $this->groupRepository->LoadById($groupId);
		$group->RemoveUser($userId);
		$this->groupRepository->Update($group);
	}

	protected function AddGroup()
	{
		$groupName = $this->page->GetGroupName();
		Log::Debug('Adding new group with name: %s', $groupName);

		$group = new Group(0, $groupName);
		$this->groupRepository->Add($group);
	}

	protected function RenameGroup()
	{
		$groupId = $this->page->GetGroupId();
		$groupName = $this->page->GetGroupName();
		Log::Debug('Renaming group id: %s to: %s', $groupId, $groupName);

		$group = $this->groupRepository->LoadById($groupId);
		$group->Rename($groupName);
		
		$this->groupRepository->Update($group);
	}
	
	protected function DeleteGroup()
	{
		$groupId = $this->page->GetGroupId();

		Log::Debug("Deleting groupId: %s", $groupId);

		$group = $this->groupRepository->LoadById($groupId);
		$this->groupRepository->Remove($group);
	}

	/**
	 * @return array|int[]
	 */
	private function GetGroupRoles()
	{
		$groupId = $this->page->GetGroupId();
		$group = $this->groupRepository->LoadById($groupId);

		$roles = $group->Roles();

		$ids = array();

		/** @var $role RoleDto */
		foreach ($roles as $role)
		{
			$ids[] = $role->Id;
		}

		return $ids;
	}
}

class UserGroupResults
{
	public function __construct($users, $totalUsers)
	{
		$this->Users = $users;
		$this->Total = $totalUsers;
	}

	public $Total;
	public $Users;
}

?>