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

require_once(ROOT_DIR . 'Pages/Page.php');
require_once(ROOT_DIR . 'Presenters/ResourcePresenter.php');
require_once(ROOT_DIR . 'Domain/Access/namespace.php');

interface IResourcePage extends IPage
{
	public function SaveClicked();
	public function IsEditingResource();
		
	public function SetResourceName($resourceName);
	public function SetLocation($location);
	public function SetContactInfo($contactInfo);
	public function SetDescription($description);
	public function SetNotes($notes);	
	public function SetIsActive($isActive);
	public function SetMinDuration($minDuration);
	public function SetMinIncrement($minIncrement);
	public function SetMaxDuration($maxDuration);
	public function SetUnitCost($unitCost);
	public function SetAutoAssign($autoAssign);
	public function SetRequiresApproval($requiresApproval);
	public function SetAllowMultiday($allowMultiday);
	public function SetMaxParticipants($maxParticipants);
	public function SetMinNotice($minNotice);
	public function SetMaxNotice($maxNotice);
	
	public function GetResourceId();
	public function GetResourceName();
	public function GetLocation();
	public function GetContactInfo();
	public function GetDescription();
	public function GetNotes();	
	public function GetIsActive();
	public function GetMinDuration();
	public function GetMinIncrement();
	public function GetMaxDuration();
	public function GetUnitCost();
	public function GetAutoAssign();
	public function GetRequiresApproval();
	public function GetAllowMultiday();
	public function GetMaxParticipants();
	public function GetMinNotice();
	public function GetMaxNotice();
}

class ResourcePage extends Page implements IResourcePage
{
	public function __construct()
	{
		parent::__construct('Resource');
		
		$this->_presenter = new ResourcePresenter($this, new ResourceRepository());			
	}
	
	public function PageLoad()
	{
		$this->_presenter->PageLoad();
		
		$this->smarty->display('resource.tpl');				
	}
	
	public function SaveClicked()
	{
		return $this->GetForm(Actions::SAVE);
	}
	
	public function IsEditingResource()
	{
		return $this->GetQuerystring(QueryStringKeys::RESOURCE_ID) != null;
	}

	public function SetResourceName($resourceName)
	{
		$this->Set('ResourceName', $resourceName);	
	}

	public function SetLocation($location)
	{
		$this->Set('Location', $location);	
	}

	public function SetContactInfo($contactInfo)
	{
		$this->Set('ContactInfo', $contactInfo);	
	}

	public function SetDescription($description)
	{
		$this->Set('Description', $description);	
	}

	public function SetNotes($notes)	
	{
		$this->Set('Notes', $notes);	
	}

	public function SetIsActive($isActive)
	{
		$this->Set('IsActive', $isActive);	
	}

	public function SetMinDuration($minDuration)
	{
		$this->Set('MinimumDuration', $minDuration);	
	}

	public function SetMinIncrement($minIncrement)
	{
		$this->Set('MinimumIncrement', $minIncrement);	
	}

	public function SetMaxDuration($maxDuration)
	{
		$this->Set('MaximumDuration', $maxDuration);	
	}

	public function SetUnitCost($unitCost)
	{
		$this->Set('UnitCost', $unitCost);	
	}

	public function SetAutoAssign($autoAssign)
	{
		$this->Set('AutoAssign', $autoAssign);	
	}

	public function SetRequiresApproval($requiresApproval)
	{
		$this->Set('RequiresApproval', $requiresApproval);	
	}

	public function SetAllowMultiday($allowMultiday)
	{
		$this->Set('AllowMultiday', $allowMultiday);	
	}

	public function SetMaxParticipants($maxParticipants)
	{
		$this->Set('MaximumParticipants', $maxParticipants);	
	}

	public function SetMinNotice($minNotice)
	{
		$this->Set('MinimumNotice', $minNotice);	
	}

	public function SetMaxNotice($maxNotice)
	{
		$this->Set('MaximumNotice', $maxNotice);	
	}
	
	public function GetResourceId()
	{
		return $this->GetQuerystring(QueryStringKeys::RESOURCE_ID);
	}

	public function GetResourceName()
	{
		return $this->GetForm(FormKeys::RESOURCE_NAME);
	}
	
	public function GetLocation()
	{
		return $this->GetForm(FormKeys::RESOURCE_LOCATION);
	}
	
	public function GetContactInfo()
	{
		return $this->GetForm(FormKeys::CONTACT_INFO);
	}
	
	public function GetDescription()
	{
		return $this->GetForm(FormKeys::DESCRIPTION);
	}
	
	public function GetNotes()
	{
		return $this->GetForm(FormKeys::RESOURCE_NOTES);
	}
	
	public function GetIsActive()
	{
		return $this->GetForm(FormKeys::IS_ACTIVE);
	}
	
	public function GetMinDuration()
	{
		return $this->GetForm(FormKeys::MIN_DURATION);
	}
	
	public function GetMinIncrement()
	{
		return $this->GetForm(FormKeys::MIN_INCREMENT);
	}
	
	public function GetMaxDuration()
	{
		return $this->GetForm(FormKeys::MAX_DURATION);
	}
	
	public function GetUnitCost()
	{
		return $this->GetForm(FormKeys::UNIT_COST);
	}

	public function GetAutoAssign()
	{
		return $this->GetForm(FormKeys::AUTO_ASSIGN);
	}
	
	public function GetRequiresApproval()
	{
		return $this->GetForm(FormKeys::REQUIRES_APPROVAL);
	}
	
	public function GetAllowMultiday()
	{
		return $this->GetForm(FormKeys::ALLOW_MULTIDAY);
	}
	
	public function GetMaxParticipants()
	{
		return $this->GetForm(FormKeys::MAX_PARTICIPANTS);
	}
	
	public function GetMinNotice()
	{
		return $this->GetForm(FormKeys::MIN_NOTICE);
	}
	
	public function GetMaxNotice()
	{
		return $this->GetForm(FormKeys::MAX_NOTICE);
	}
}
?>