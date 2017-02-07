<?php

/**
 * Access Control Lists Custom Query generator class
 *
 * This class is basically an Utility class used to generate queries needed for overriding addSelectWhereClause
 * for some specific BAO's
 *
 */
class CRM_HRLeaveAndAbsences_API_Query_ACLQueryHelper {

  /**
   * This method returns a query string that limits contacts records that are available to a logged in user
   * by taking into account user relationships. It checks whether the logged in user is a leave approver
   * to some contacts or not. If yes the logged in user has access to contact records for the contacts/users he manages,
   * If not the logged is limited to his/her own records only.
   *
   * @return string
   *   Query String
   */
  public static function limitContactsByTakingUserRelationShipsIntoAccount() {
    $contactsTable = CRM_Contact_BAO_Contact::getTableName();
    $relationshipTable = CRM_Contact_BAO_Relationship::getTableName();
    $relationshipTypeTable = CRM_Contact_BAO_RelationshipType::getTableName();
    $loggedInUserID = (int) CRM_Core_Session::getLoggedInContactID();
    $today = date('Y-m-d');

    $conditions = [];
    $conditions[] = "(c.id = {$loggedInUserID})";

    $leaveApproverRelationships = self::getLeaveApproverRelationships();
    if(!empty($leaveApproverRelationships)) {
      $conditions[] = "(
        r.is_active = 1 AND
        rt.is_active = 1 AND
        rt.id IN(" . implode(',', $leaveApproverRelationships) . ") AND
        r.contact_id_b = {$loggedInUserID} AND 
        (r.start_date IS NULL OR r.start_date <= '$today') AND
        (r.end_date IS NULL OR r.end_date >= '$today')
      )";
    }

    $conditions = implode(' OR ', $conditions);

    $query = "IN (
      SELECT c.id
      FROM {$contactsTable} c
      LEFT JOIN {$relationshipTable} r ON c.id = r.contact_id_a
      LEFT JOIN {$relationshipTypeTable} rt ON rt.id = r.relationship_type_id
      WHERE $conditions
    )";

    return $query;
  }

  /**
   * Returns a list of relationship types stored on the
   * 'relationship_types_allowed_to_approve_leave' setting
   *
   * @return array
   */
  private static function getLeaveApproverRelationships() {
    return Civi::service('hrleaveandabsences.settings_manager')->get('relationship_types_allowed_to_approve_leave');
  }
}