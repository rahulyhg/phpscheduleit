<?php 
require_once(ROOT_DIR . 'Domain/namespace.php');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'lib/Graphics/namespace.php');
require_once(ROOT_DIR . 'Presenters/ActionPresenter.php');

class ManageResourcesActions
{
	const ActionAdd = 'add';
	const ActionChangeConfiguration = 'configuration';
	const ActionChangeDescription = 'description';
	const ActionChangeImage = 'image';
	const ActionChangeLocation = 'location';
	const ActionChangeNotes = 'notes';
	const ActionChangeSchedule = 'schedule';
	const ActionRemoveImage = 'removeImage';
	const ActionRename = 'rename';
	const ActionDelete = 'delete';
	const ActionBringOnline = "bringOnline";
	const ActionTakeOffline = "takeOffline";
}


class ManageResourcesPresenter extends ActionPresenter
{
	/**
	 * @var IManageResourcesPage
	 */
	private $page;

	/**
	 * @var IResourceRepository
	 */
	private $resourceRepository;

	/**
	 * @var IScheduleRepository
	 */
	private $scheduleRepository;

	/**
	 * @var IImageFactory
	 */
	private $imageFactory;

	private $actions = array();

	public function __construct(
		IManageResourcesPage $page,
		IResourceRepository $resourceRepository,
		IScheduleRepository $scheduleRepository,
		IImageFactory $imageFactory)
	{
		parent::__construct($page);

		$this->page = $page;
		$this->resourceRepository = $resourceRepository;
		$this->scheduleRepository = $scheduleRepository;
		$this->imageFactory = $imageFactory;

		$this->AddAction(ManageResourcesActions::ActionAdd, 'Add');
		$this->AddAction(ManageResourcesActions::ActionChangeConfiguration, 'ChangeConfiguration');
		$this->AddAction(ManageResourcesActions::ActionChangeDescription, 'ChangeDescription');
		$this->AddAction(ManageResourcesActions::ActionChangeImage, 'ChangeImage');
		$this->AddAction(ManageResourcesActions::ActionChangeLocation, 'ChangeLocation');
		$this->AddAction(ManageResourcesActions::ActionChangeNotes, 'ChangeNotes');
		$this->AddAction(ManageResourcesActions::ActionChangeSchedule, 'ChangeSchedule');
		$this->AddAction(ManageResourcesActions::ActionRemoveImage, 'RemoveImage');
		$this->AddAction(ManageResourcesActions::ActionRename, 'Rename');
		$this->AddAction(ManageResourcesActions::ActionDelete, 'Delete');
		$this->AddAction(ManageResourcesActions::ActionTakeOffline, 'TakeOffline');
		$this->AddAction(ManageResourcesActions::ActionBringOnline, 'BringOnline');
	}

	public function PageLoad()
	{
		$resources = $this->resourceRepository->GetResourceList();
		$schedules = $this->scheduleRepository->GetAll();

		$this->page->BindResources($resources);

		$scheduleList = array();

		/* @var $schedule Schedule */
		foreach ($schedules as $schedule)
		{
			$scheduleList[$schedule->GetId()] = $schedule->GetName();
		}
		$this->page->BindSchedules($scheduleList);
	}

	/**
	 * @internal should only be used for testing
	 */
	public function Add()
	{
		$name = $this->page->GetResourceName();
		$scheduleId = $this->page->GetScheduleId();

		$resource = BookableResource::CreateNew($name, $scheduleId);
		$this->resourceRepository->Add($resource);
	}

	/**
	 * @internal should only be used for testing
	 */
	public function ChangeConfiguration()
	{
		$minDuration = $this->page->GetMinimumDuration();
		$maxDuration = $this->page->GetMaximumDuration();
		$allowMultiDay = $this->page->GetAllowMultiday();
		$requiresApproval = $this->page->GetRequiresApproval();
		$autoAssign = $this->page->GetAutoAssign();
		$minNotice = $this->page->GetStartNoticeMinutes();
		$maxNotice = $this->page->GetEndNoticeMinutes();
		$maxParticipants = $this->page->GetMaxParticipants();

		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());

		$resource->SetMinLength($minDuration);
		$resource->SetMaxLength($maxDuration);
		$resource->SetAllowMultiday($allowMultiDay);
		$resource->SetRequiresApproval($requiresApproval);
		$resource->SetAutoAssign($autoAssign);
		$resource->SetMinNotice($minNotice);
		$resource->SetMaxNotice($maxNotice);
		$resource->SetMaxParticipants($maxParticipants);

		$this->resourceRepository->Update($resource);
	}

	/**
	 * @internal should only be used for testing
	 */
	public function Delete()
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());
		$this->resourceRepository->Delete($resource);
	}

	/**
	 * @internal should only be used for testing
	 */
	public function ChangeDescription()
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());

		$resource->SetDescription($this->page->GetDescription());

		$this->resourceRepository->Update($resource);
	}

	/**
	 * @internal should only be used for testing
	 */
	public function ChangeNotes()
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());

		$resource->SetNotes($this->page->GetNotes());

		$this->resourceRepository->Update($resource);
	}

	/**
	 * @internal should only be used for testing
	 */
	public function Rename()
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());

		$resource->SetName($this->page->GetResourceName());

		$this->resourceRepository->Update($resource);
	}

	/**
	 * @internal should only be used for testing
	 */
	public function ChangeLocation()
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());

		$resource->SetLocation($this->page->GetLocation());
		$resource->SetContact($this->page->GetContact());

		$this->resourceRepository->Update($resource);
	}

	public function ChangeImage()
	{
		$uploadedImage = $this->page->GetUploadedImage();

		if ($uploadedImage->IsError()) {
			die("Image error: " . $uploadedImage->Error());
		}

		$fileType = strtolower($uploadedImage->Extension());

		$supportedTypes = array('jpeg', 'gif', 'png', 'jpg');

		if (!in_array($fileType, $supportedTypes)) {
			die("Invalid image type: $fileType");
		}

		$image = $this->imageFactory->Load($uploadedImage->TemporaryName());
		$image->ResizeToWidth(300);

		$fileName = "resource{$this->page->GetResourceId()}.$fileType";
		$path = ROOT_DIR . Configuration::Instance()->GetKey(ConfigKeys::IMAGE_UPLOAD_DIRECTORY) . "/$fileName";
		$image->Save($path);

		$this->SaveResourceImage($fileName);
	}

	public function RemoveImage()
	{
		$this->SaveResourceImage(null);
	}

	public function TakeOffline()
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());
		$resource->TakeOffline();
		$this->resourceRepository->Update($resource);
	}

	public function BringOnline()
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());
		$resource->BringOnline();
		$this->resourceRepository->Update($resource);
	}

	private function SaveResourceImage($fileName)
	{
		$resource = $this->resourceRepository->LoadById($this->page->GetResourceId());

		$resource->SetImage($fileName);

		$this->resourceRepository->Update($resource);
	}
}

?>