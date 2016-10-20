<?php

/**
 * Class CRM_CiviHRSampleData_Importer_VacancyValue
 *
 */
class CRM_CiviHRSampleData_Importer_VacancyValue extends CRM_CiviHRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('application_case');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('case_mapping', $row['entity_id']);

    $row['vacancy_id'] = $this->getDataMapping('vacancy_mapping', $row['vacancy_id']);;

    parent::insertRecord($row);
  }

}
