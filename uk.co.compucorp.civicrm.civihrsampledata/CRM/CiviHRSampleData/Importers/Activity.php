<?php

/**
 * Class CRM_CiviHRSampleData_Importer_Activity
 *
 */
class CRM_CiviHRSampleData_Importer_Activity extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $currentID = $this->unsetArrayElement($row, 'id');

    if (!empty($row['source_record_id'])) {
      $row['source_record_id'] = $this->getDataMapping('activity_mapping', $row['source_record_id']);
    }

    $row['source_contact_id'] = $this->getDataMapping('contact_mapping', $row['source_contact_id']);

    if (!empty($row['assignee_id'])) {
      $row['assignee_id'] = $this->getDataMapping('contact_mapping', $row['assignee_id']);
    }

    if (!empty($row['target_id'])) {
      $row['target_id'] = $this->getDataMapping('contact_mapping', $row['target_id']);
    }

    $result = $this->callAPI('Activity', 'create', $row);

    $this->setDataMapping('activity_mapping', $currentID, $result['id']);
  }

}
