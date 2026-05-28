<?php

class CRM_Bandcpm_ImportTimesheet {
  private $contactIdCache = [];

  public function run(string $filePath) {
    $separator = CRM_Core_Config::singleton()->fieldSeparator ?: ',';

    $handle = fopen($filePath, 'r');
    if (!$handle) {
      throw new CRM_Core_Exception("Could not read CSV file");
    }

    $headers = fgetcsv($handle, 0, $separator, '"', '');

    while (($row = fgetcsv($handle, 0, $separator, '"', '')) !== FALSE) {
      $row = array_map(['CRM_Import_DataSource', 'trimWhitespace'], $row);
      $record = array_combine($headers, $row);

      echo "Importing {$record['Project']} {$record['Start Date']} {$record['Start Time']}\n";

      $contactId = $this->getContactIdFromProject($record['Project']);

      \Civi\Api4\CustomValue::create('Timesheet', FALSE)
        ->addValue('Date', $this->formatDate($record['Start Date']))
        ->addValue('From', $this->formatTime($record['Start Time']))
        ->addValue('To', $this->formatTime($record['End Time']))
        ->addValue('Description', $record['Description'])
        ->addValue('Total', $this->formatTime($record['Duration (h)']))
        ->addValue('Total_decimal_', $this->formatMoney($record['Duration (decimal)']))
        ->addValue('Hourly_Fee', $this->formatMoney($record['Billable Rate (EUR)']))
        ->addValue('Total_Amount', $this->formatMoney($record['Billable Amount (EUR)']))
        ->addValue('entity_id', $contactId)
        ->execute();
    }

    fclose($handle);

  }

  private function getContactIdFromProject(string $projectName): int {
    if (!empty($this->contactIdCache[$projectName])) {
      return $this->contactIdCache[$projectName];
    }

    $contact = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id')
      ->addWhere('display_name', '=', $projectName)
      ->execute()
      ->first();

    if ($contact) {
      $this->contactIdCache[$projectName] = $contact['id'];
      return $contact['id'];
    }

    die("Cannot find $projectName\n");
  }

  private function formatDate($date) {
    return substr($date, 6, 4) . '-' . substr($date, 3, 2) . '-' . substr($date, 0, 2);
  }

  private function formatTime($time) {
    if (strlen($time) === 4) {
      return '0' . $time;
    }
    return $time;
  }

  private function formatMoney($m) {
    return str_replace(',', '.', $m);
  }

}
