<?php
/*
+--------------------------------------------------------------------+
| CiviCRM version 4.7                                                |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2017                                |
+--------------------------------------------------------------------+
| This file is a part of CiviCRM.                                    |
|                                                                    |
| CiviCRM is free software; you can copy, modify, and distribute it  |
| under the terms of the GNU Affero General Public License           |
| Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
|                                                                    |
| CiviCRM is distributed in the hope that it will be useful, but     |
| WITHOUT ANY WARRANTY; without even the implied warranty of         |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
| See the GNU Affero General Public License for more details.        |
|                                                                    |
| You should have received a copy of the GNU Affero General Public   |
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/
/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2017
 *
 * Generated from xml/schema/CRM/HRLeaveAndAbsences/AbsenceType.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:894f42b522069204b99d7631796928f5)
 */
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
/**
 * CRM_HRLeaveAndAbsences_DAO_AbsenceType constructor.
 */
class CRM_HRLeaveAndAbsences_DAO_AbsenceType extends CRM_Core_DAO {
  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  static $_tableName = 'civicrm_hrleaveandabsences_absence_type';
  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var boolean
   */
  static $_log = true;
  /**
   * Unique AbsenceType ID
   *
   * @var int unsigned
   */
  public $id;
  /**
   * The AbsenceType title. There can't be more than one entity with the same title
   *
   * @var string
   */
  public $title;
  /**
   * The weight value is used to order the types on a list
   *
   * @var int unsigned
   */
  public $weight;
  /**
   * The color hex value (including the #) used to display this type on a calendar
   *
   * @var string
   */
  public $color;
  /**
   * There can only be one default entity at any given time
   *
   * @var boolean
   */
  public $is_default;
  /**
   * Reserved entities are used by internal calculations and cannot be deleted.
   *
   * @var boolean
   */
  public $is_reserved;
  /**
   *
   * @var float
   */
  public $max_consecutive_leave_days;
  /**
   * Can only be one of the values defined in AbsenceType::REQUEST_CANCELATION_OPTIONS
   *
   * @var int unsigned
   */
  public $allow_request_cancelation;
  /**
   *
   * @var boolean
   */
  public $allow_overuse;
  /**
   *
   * @var boolean
   */
  public $must_take_public_holiday_as_leave;
  /**
   * The number of days entitled for this type
   *
   * @var float
   */
  public $default_entitlement;
  /**
   *
   * @var boolean
   */
  public $add_public_holiday_to_entitlement;
  /**
   * Only enabled types can be requested
   *
   * @var boolean
   */
  public $is_active;
  /**
   *
   * @var boolean
   */
  public $allow_accruals_request;
  /**
   * Value is the number of days that can be accrued. Null means unlimited
   *
   * @var float
   */
  public $max_leave_accrual;
  /**
   *
   * @var boolean
   */
  public $allow_accrue_in_the_past;
  /**
   * An amount of accrual_expiration_unit
   *
   * @var int unsigned
   */
  public $accrual_expiration_duration;
  /**
   * The unit (months or days) of accrual_expiration_duration of this type default expiry
   *
   * @var int unsigned
   */
  public $accrual_expiration_unit;
  /**
   *
   * @var boolean
   */
  public $allow_carry_forward;
  /**
   *
   * @var float
   */
  public $max_number_of_days_to_carry_forward;
  /**
   * An amount of carry_forward_expiration_unit
   *
   * @var int unsigned
   */
  public $carry_forward_expiration_duration;
  /**
   * The unit (months or days) of carry_forward_expiration_duration of this type default expiry
   *
   * @var int unsigned
   */
  public $carry_forward_expiration_unit;
  /**
   * A flag which is used to determine if this Absence Type can be used for a Sickness Request
   *
   * @var boolean
   */
  public $is_sick;
  /**
   * One of the values of the Absence type calculation units option group
   *
   * @var string
   */
  public $calculation_unit;
  /**
   * Class constructor.
   */
  function __construct() {
    $this->__table = 'civicrm_hrleaveandabsences_absence_type';
    parent::__construct();
  }
  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => 'Unique AbsenceType ID',
          'required' => true,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'title' => array(
          'name' => 'title',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Title') ,
          'description' => 'The AbsenceType title. There can\'t be more than one entity with the same title',
          'required' => true,
          'maxlength' => 127,
          'size' => CRM_Utils_Type::HUGE,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'weight' => array(
          'name' => 'weight',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Weight') ,
          'description' => 'The weight value is used to order the types on a list',
          'required' => true,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'color' => array(
          'name' => 'color',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Colour') ,
          'description' => 'The color hex value (including the #) used to display this type on a calendar',
          'required' => true,
          'maxlength' => 7,
          'size' => CRM_Utils_Type::EIGHT,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'is_default' => array(
          'name' => 'is_default',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Is default?') ,
          'description' => 'There can only be one default entity at any given time',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'is_reserved' => array(
          'name' => 'is_reserved',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Is reserved?') ,
          'description' => 'Reserved entities are used by internal calculations and cannot be deleted.',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'max_consecutive_leave_days' => array(
          'name' => 'max_consecutive_leave_days',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Duration of consecutive leave permitted to be taken at once') ,
          'precision' => array(
            20,
            2
          ) ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'allow_request_cancelation' => array(
          'name' => 'allow_request_cancelation',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Can staff cancel requests for this leave type after they have been made?') ,
          'description' => 'Can only be one of the values defined in AbsenceType::REQUEST_CANCELATION_OPTIONS',
          'required' => true,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'allow_overuse' => array(
          'name' => 'allow_overuse',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Can employee apply for this leave type even if they have used up their entitlement for the year?') ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'must_take_public_holiday_as_leave' => array(
          'name' => 'must_take_public_holiday_as_leave',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Must staff take public holiday as leave') ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'default_entitlement' => array(
          'name' => 'default_entitlement',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Default entitlement') ,
          'description' => 'The number of days entitled for this type',
          'required' => true,
          'precision' => array(
            20,
            2
          ) ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'add_public_holiday_to_entitlement' => array(
          'name' => 'add_public_holiday_to_entitlement',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('By default should public holiday be added to the default entitlement?') ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'is_active' => array(
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Is enabled?') ,
          'description' => 'Only enabled types can be requested',
          'default' => '1',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'allow_accruals_request' => array(
          'name' => 'allow_accruals_request',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Allow staff to request to accrue additional days leave of this type during the period') ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'max_leave_accrual' => array(
          'name' => 'max_leave_accrual',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Maximum amount of leave that can be accrued of this absence type during a period') ,
          'description' => 'Value is the number of days that can be accrued. Null means unlimited',
          'precision' => array(
            20,
            2
          ) ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'allow_accrue_in_the_past' => array(
          'name' => 'allow_accrue_in_the_past',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Can staff request to accrue leave for dates in the past?') ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'accrual_expiration_duration' => array(
          'name' => 'accrual_expiration_duration',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Default expiry of accrued amounts') ,
          'description' => 'An amount of accrual_expiration_unit',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'accrual_expiration_unit' => array(
          'name' => 'accrual_expiration_unit',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Accrual Expiration Unit') ,
          'description' => 'The unit (months or days) of accrual_expiration_duration of this type default expiry',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'allow_carry_forward' => array(
          'name' => 'allow_carry_forward',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Allow leave of this type to be carried forward from one period to another?') ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'max_number_of_days_to_carry_forward' => array(
          'name' => 'max_number_of_days_to_carry_forward',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Maximum number of days that can be carried forward to a new period?') ,
          'precision' => array(
            20,
            2
          ) ,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'carry_forward_expiration_duration' => array(
          'name' => 'carry_forward_expiration_duration',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Carried forward leave expiry') ,
          'description' => 'An amount of carry_forward_expiration_unit',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'carry_forward_expiration_unit' => array(
          'name' => 'carry_forward_expiration_unit',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Carry Forward Expiration Unit') ,
          'description' => 'The unit (months or days) of carry_forward_expiration_duration of this type default expiry',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'is_sick' => array(
          'name' => 'is_sick',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'description' => 'A flag which is used to determine if this Absence Type can be used for a Sickness Request',
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
        ) ,
        'calculation_unit' => array(
          'name' => 'calculation_unit',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Calculation Unit') ,
          'description' => 'One of the values of the Absence type calculation units option group',
          'required' => true,
          'maxlength' => 512,
          'size' => CRM_Utils_Type::HUGE,
          'table_name' => 'civicrm_hrleaveandabsences_absence_type',
          'entity' => 'AbsenceType',
          'bao' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
          'localizable' => 0,
          'pseudoconstant' => array(
            'optionGroupName' => 'hrleaveandabsences_absence_type_calculation_unit',
            'optionEditPath' => 'civicrm/admin/options/hrleaveandabsences_absence_type_calculation_unit',
          )
        ) ,
      );
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }
  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }
  /**
   * Returns the names of this table
   *
   * @return string
   */
  static function getTableName() {
    return self::$_tableName;
  }
  /**
   * Returns if this table needs to be logged
   *
   * @return boolean
   */
  function getLog() {
    return self::$_log;
  }
  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  static function &import($prefix = false) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'hrleaveandabsences_absence_type', $prefix, array());
    return $r;
  }
  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  static function &export($prefix = false) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'hrleaveandabsences_absence_type', $prefix, array());
    return $r;
  }
  /**
   * Returns the list of indices
   */
  public static function indices($localize = TRUE) {
    $indices = array();
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }
}
