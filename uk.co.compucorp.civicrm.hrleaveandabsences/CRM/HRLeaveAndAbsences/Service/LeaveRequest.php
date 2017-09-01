<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix as LeaveRequestStatusMatrixService;

class CRM_HRLeaveAndAbsences_Service_LeaveRequest {

  /**
   * @var \LeaveBalanceChangeService
   */
  protected $leaveBalanceChangeService;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix
   */
  private $leaveRequestStatusMatrixService;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveRequestRights
   */
  private $leaveRequestRightsService;

  /**
   * @var \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   *   The leave request object before it gets updated.
   */
  protected $oldLeaveRequest;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest status_id field.
   */
  private $leaveStatuses;

  /**
   * @var boolean|null
   *   Stores whether the dates has changed for the
   *   current leave request to be updated or not.
   */
  private $datesChanged = null;

  /**
   * CRM_HRLeaveAndAbsences_Service_LeaveRequest constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange $leaveBalanceChangeService
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix $leaveRequestStatusMatrixService
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveRequestRights $leaveRequestRightsService
   */
  public function __construct(
    LeaveBalanceChangeService $leaveBalanceChangeService,
    LeaveRequestStatusMatrixService $leaveRequestStatusMatrixService,
    LeaveRequestRightsService $leaveRequestRightsService
  ) {
    $this->leaveBalanceChangeService = $leaveBalanceChangeService;
    $this->leaveRequestStatusMatrixService = $leaveRequestStatusMatrixService;
    $this->leaveRequestRightsService = $leaveRequestRightsService;
  }

  /**
   * Wraps the LeaveRequest BAO create method in order to perform some more
   * actions before/after calling it.
   *
   * @param array $params
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   */
  public function create($params, $validate = true) {
    if ($validate) {
      $this->runValidation($params);
    }

    if(!$this->canCreateAndUpdateLeaveRequestFor($params['contact_id'])) {
      throw new RuntimeException('You are not allowed to create or update a leave request for this employee');
    }

    if(!empty($params['id'])) {
      return $this->update($params);
    }

    if(!$this->isValidStatusTransition('', $params['status_id'], $params['contact_id'])) {
      throw new RuntimeException("You can't create a Leave Request with this status");
    }

    return $this->createRequestWithBalanceChanges($params);
  }

  /**
   * Performs some checks/validations required
   *
   * @param array $params
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   */
  protected function update($params) {
    if ($this->datesChanged($params) && !$this->canChangeDatesFor($params)) {
      throw new RuntimeException('You are not allowed to change the request dates');
    }

    if ($this->absenceTypeChanged($params) && !$this->canChangeAbsenceTypeFor($params)) {
      throw new RuntimeException('You are not allowed to change the type of a request');
    }

    if ($this->statusChanged($params) && !$this->isValidStatusTransition(
      $this->getCurrentStatus($params), $params['status_id'], $params['contact_id'])
    ) {
      throw new RuntimeException(
        "You can't change the Leave Request status from ".
        $this->getCurrentStatus($params). " to {$params['status_id']}"
      );
    }

    return $this->createRequestWithBalanceChanges($params);
  }

  /**
   * Soft Deletes the LeaveRequest with the given $leaveRequestID
   *
   * @param int $leaveRequestID
   */
  public function delete($leaveRequestID) {
    $leaveRequest = LeaveRequest::findById($leaveRequestID);

    if(!$this->canDeleteLeaveRequestFor($leaveRequest->contact_id)) {
      throw new RuntimeException('You are not allowed to delete a leave request for this employee');
    }

    LeaveRequest::softDelete($leaveRequestID);
  }

  /**
   * Checks if the status transition from $fromStatus to $toStatus is possible
   * using the method provided by the leaveRequestStatusMatrix service.
   *
   * @param int $fromStatus
   * @param int $toStatus
   * @param int $contactID
   *
   * @return bool
   */
  private function isValidStatusTransition($fromStatus, $toStatus, $contactID) {
    return $this->leaveRequestStatusMatrixService->canTransitionTo($fromStatus, $toStatus, $contactID);
  }

  /**
   * Checks if the current user can create/update a leave request
   *
   * @param int $contactID
   *
   * @return bool
   */
  private function canCreateAndUpdateLeaveRequestFor($contactID) {
    return $this->leaveRequestRightsService->canCreateAndUpdateFor($contactID);
  }

  /**
   * Checks if the current user can change/update the leave request dates
   *
   * @param array $params
   *
   * @return bool
   */
  protected function canChangeDatesFor($params) {
    return $this->leaveRequestRightsService->canChangeDatesFor(
      $params['contact_id'],
      $params['status_id'],
      $params['request_type']
    );
  }

  /**
   * Checks if the current user can update the absence type of a leave request
   *
   * @param array $params
   *
   * @return bool
   */
  private function canChangeAbsenceTypeFor($params) {
    return $this->leaveRequestRightsService->canChangeAbsenceTypeFor($params['contact_id'], $params['status_id']);
  }

  /**
   * Checks if the current user can delete leave requests for the given $contactID
   *
   * @param int $contactID
   *
   * @return bool
   */
  private function canDeleteLeaveRequestFor($contactID) {
    return $this->leaveRequestRightsService->canDeleteFor($contactID);
  }

  /**
   * Checks if the from_date or to_date of a leave request has changed by comparing the
   * date values to be updated to the current values in the database
   *
   * @param array $params
   *
   * @return bool
   */
  private function datesChanged($params) {
    if (is_null($this->datesChanged)) {
      $this->datesChanged = LeaveRequest::datesChanged($params);
    }
    return $this->datesChanged;
  }

  /**
   * Checks if absence type of a leave request has changed by comparing the
   * value to be updated to the current absence type value in the database
   *
   * @param array $params
   *
   * @return bool
   */
  private function absenceTypeChanged($params) {
    $oldLeaveRequest = $this->getOldLeaveRequest($params['id']);
    return $oldLeaveRequest->type_id != $params['type_id'];
  }

  /**
   * Checks if the status of a leave request has changed by comparing the
   * value to be updated to the current status_id value in the database
   *
   * @param array $params
   *
   * @return bool
   */
  private function statusChanged($params) {
    $oldLeaveRequest = $this->getOldLeaveRequest($params['id']);
    return $oldLeaveRequest->status_id != $params['status_id'];
  }

  /**
   * Returns the current status of the leave request before it gets updated
   *
   * @param array $params
   *
   * @return int
   */
  private function getCurrentStatus($params) {
    $oldLeaveRequest = $this->getOldLeaveRequest($params['id']);
    return $oldLeaveRequest->status_id;
  }

  /**
   * Returns the LeaveRequest object in its current state (i.e before it gets updated)
   *
   * @param int $leaveRequestID
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  protected function getOldLeaveRequest($leaveRequestID) {
    if (!$this->oldLeaveRequest) {
      $this->oldLeaveRequest = LeaveRequest::findById($leaveRequestID);
    }
    return $this->oldLeaveRequest;
  }

  /**
   * Creates/Updates a leave request along with it's balance changes
   *
   * @param array $params
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   */
  protected function createRequestWithBalanceChanges($params) {
    $updateBalanceChange = !empty($params['change_balance']);
    $skipBalanceChangeUpdate = !empty($params['id']) &&
      !$this->datesChanged($params) && !$updateBalanceChange;
    $leaveRequest = LeaveRequest::create($params, false);

    if(!$skipBalanceChangeUpdate) {
      $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);
    }
    $this->recalculateExpiredBalanceChange($leaveRequest);

    return $leaveRequest;
  }

  /**
   * Run necessary BAO validations
   *
   * @param array $params
   *
   * @throws CRM_HRLeaveAndAbsences_Exception_EntityValidationException
   */
  protected function runValidation($params) {
    LeaveRequest::validateParams($params);
  }

  /**
   * Recalculates expired TOIL/Brought Forward balance changes for
   * a leave request with past dates having expired LeaveBalanceChanges that expired on or after the
   * LeaveRequest past date.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   */
  private function recalculateExpiredBalanceChange(LeaveRequest $leaveRequest) {
    $leaveStatuses = $this->getLeaveRequestStatuses();
    $today = new DateTime();
    $leaveRequestDate = new DateTime($leaveRequest->from_date);

    if($leaveRequestDate < $today && $leaveRequest->status_id == $leaveStatuses['approved']) {
      $this->leaveBalanceChangeService->recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
    }
  }

  /**
   * Returns the array of the option values for the LeaveRequest status_id field.
   *
   * @return array
   */
  private function getLeaveRequestStatuses() {
    if (is_null($this->leaveStatuses)) {
      $this->leaveStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));
    }

    return $this->leaveStatuses;
  }
}
