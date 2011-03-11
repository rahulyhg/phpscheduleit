<?php
require_once(ROOT_DIR . 'lib/Application/Reservation/Validation/IReservationValidationService.php');

interface IUpdateReservationValidationService extends IReservationValidationService
{
	/**
	 * @param ExistingReservationSeries $reservationSeries
	 * @return IReservationValidationResult
	 */
	//function Validate($reservationSeries);
}

class UpdateReservationValidationService implements IUpdateReservationValidationService
{
	/**
	 * @var IExistingReservationValidationRule[]
	 */
	private $_validationRules;
	
	/**
	 * @param IExistingReservationValidationRule[] $validationRules
	 */
	public function __construct($validationRules)
	{
		$this->_validationRules = $validationRules;	
	}
	
	/**
	 * @param ExistingReservationSeries $reservationSeries
	 */
	public function Validate($reservationSeries)
	{
		foreach ($this->_validationRules as $rule)
		{
			$result = $rule->Validate($reservationSeries);
			
			if (!$result->IsValid())
			{
				return new ReservationValidationResult(false, array($result->ErrorMessage()));
			}
		}
		
		return new ReservationValidationResult();
	}
}
?>