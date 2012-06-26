<?php
/**
Copyright 2012 Nick Korbel

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

require_once(ROOT_DIR . 'Domain/Access/namespace.php');
require_once(ROOT_DIR . 'Presenters/ActionPresenter.php');

class ManageAttributesActions
{
    const AddAttribute = 'addAttribute';
    const UpdateAttribute = 'updateAttribute';
}

class ManageAttributesPresenter extends ActionPresenter
{
	/**
	 * @var IManageAttributesPage
	 */
	private $page;

	/**
	 * @var IAttributeRepository
	 */
	private $attributeRepository;

	public function __construct(IManageAttributesPage $page, IAttributeRepository $attributeRepository)
	{
		parent::__construct($page);

		$this->page = $page;
		$this->attributeRepository = $attributeRepository;

        $this->AddAction(ManageAttributesActions::AddAttribute, 'AddAttribute');
        $this->AddAction(ManageAttributesActions::UpdateAttribute, 'UpdateAttribute');
	}

	public function PageLoad()
	{
	}

    public function AddAttribute()
    {
        $attributeName = $this->page->GetLabel();
		$type = $this->page->GetType();
		$scope = $this->page->GetCategory();
		$regex = $this->page->GetValidationExpression();
		$required = $this->page->GetIsRequired();
		$possibleValues = $this->page->GetPossibleValues();
		$sortOrder = $this->page->GetSortOrder();

        Log::Debug('Adding new attribute named: %s', $attributeName);

        $attribute = CustomAttribute::Create($attributeName, $type, $scope, $regex, $required, $possibleValues, $sortOrder);
		$this->attributeRepository->Add($attribute);
    }

	public function UpdateAttribute()
	{
		$attributeId = $this->page->GetAttributeId();
		$attributeName = $this->page->GetLabel();
		$regex = $this->page->GetValidationExpression();
		$required = $this->page->GetIsRequired();
		$possibleValues = $this->page->GetPossibleValues();
		$sortOrder = $this->page->GetSortOrder();

		Log::Debug('Updating attribute with id: %s', $attributeId);

		$attribute = $this->attributeRepository->LoadById($attributeId);
		$attribute->Update($attributeName, $regex, $required, $possibleValues, $sortOrder);

		$this->attributeRepository->Update($attribute);
	}

	public function HandleDataRequest($dataRequest)
	{
		$categoryId = $this->page->GetRequestedCategory();

		if (empty($categoryId))
		{
			$categoryId = CustomAttributeCategory::RESERVATION;
		}

		$this->page->BindAttributes($this->attributeRepository->GetByCategory($categoryId));
	}


}
?>