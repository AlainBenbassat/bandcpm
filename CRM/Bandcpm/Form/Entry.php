<?php
declare(strict_types = 1);

use CRM_Bandcpm_ExtensionUtil as E;

class CRM_Bandcpm_Form_Entry extends CRM_Core_Form {

  public function buildQuickForm(): void {
    $this->addFormFields();
    $this->setFormFieldsDefaultValues();
    $this->addFormButtons();

    $this->assign('elementNames', $this->getRenderableElementNames());

    parent::buildQuickForm();
  }

  public function validate(): bool {
    $values = $this->exportValues();

    $this->validateTimeAsString($values, 'from_7');
    $this->validateTimeAsString($values, 'to_8');

    return parent::validate();
  }

  private function validateTimeAsString(array $values, string $key): void {
    if (strlen($values[$key]) != 5 || !str_contains($values[$key], ':')) {
      $this->setElementError($key, 'Format must be hh:mm');
    }
  }

  public function postProcess(): void {
    $values = $this->exportValues();

    if ($values['hourly_fee_13'] == '') {
      $values['hourly_fee_13'] = ($values['entity_id'] == 8) ? 95.00 : 110.00;
    }

    [$fromHours, $fromMinutes] = array_map('intval', explode(':', $values['from_7']));
    [$toHours, $toMinutes] = array_map('intval', explode(':', $values['to_8']));

    $totalMinutes = (($toHours * 60) + $toMinutes) - (($fromHours * 60) + $fromMinutes);

    $values['total_10'] = sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    $values['total_decimal__11'] = round($totalMinutes / 60, 2);
    $values['total_amount_14'] = round($values['total_decimal__11'] * $values['hourly_fee_13'], 2);

    if ($values['id'] > 0) {
      $this->updateEntry($values);
    }
    else {
      $this->createEntry($values);
    }

    parent::postProcess();
  }

  private function updateEntry(array $values): void {
    $mappedValues = $this->mapEntryValues($values);
    \Civi\Api4\CustomValue::update('Timesheet', FALSE)
      ->setValues($mappedValues)
      ->addWhere('id', '=', $values['id'])
      ->execute();
  }

  private function createEntry(array $values): void {
    $mappedValues = $this->mapEntryValues($values);
    \Civi\Api4\CustomValue::create('Timesheet', FALSE)
      ->setValues($mappedValues)
      ->execute();
  }

  private function mapEntryValues(array $values): array {
    $mappedValues = [];

    $mappedValues['entity_id'] = $values['entity_id'];
    $mappedValues['Date'] = $values['date_6'];
    $mappedValues['From'] = $values['from_7'];
    $mappedValues['To'] = $values['to_8'];
    $mappedValues['Description'] = $values['description_9'];
    $mappedValues['Total'] = $values['total_10'];
    $mappedValues['Total_decimal_'] = $values['total_decimal__11'];
    $mappedValues['Hourly_Fee'] = $values['hourly_fee_13'];
    $mappedValues['Total_Amount'] = $values['total_amount_14'];

    return $mappedValues;
  }

  private function addFormFields(): void {
    $shortFieldStyle = 'width: 5em';
    $longFieldStyle = 'width: 100%;';

    $this->add('text', 'id', 'Entry ID', ['style' => $shortFieldStyle]);
    $this->addEntityRef('entity_id', 'Client', $this->getEntityRefFilterOnlyOrgs(), TRUE);
    $this->add('datepicker', 'date_6', 'Date', [], TRUE, ['time' => FALSE]);
    $this->add('text', 'description_9', 'Description', ['style' => $longFieldStyle], TRUE);
    $this->add('text', 'from_7', 'From', ['style' => $shortFieldStyle], TRUE);
    $this->add('text', 'to_8', 'To', ['style' => $shortFieldStyle], TRUE);
    $this->add('text', 'total_10', 'Total (h)', ['style' => $shortFieldStyle], FALSE);
    $this->add('text', 'total_decimal__11', 'Total (decimal)', ['style' => $shortFieldStyle], FALSE);
    $this->add('text', 'hourly_fee_13', 'Hourly fee', ['style' => $shortFieldStyle], FALSE);
    $this->add('text', 'total_amount_14', 'Total Amount', ['style' => $shortFieldStyle], FALSE);
  }

  private function addFormButtons() {
    $this->addButtons([
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ],
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
    ]);
  }

  private function setFormFieldsDefaultValues() {
    $id = (int)CRM_Utils_Request::retrieveValue('id', 'Integer', 0);
    if ($id) {
      $defaults = $this->getEntry($id);
    }
    else {
      $defaults = $this->getNewEntry();
    }

    $this->setDefaults($defaults);
  }

  private function getEntry(int $id): array {
    $defaults = [];

    $entry = \Civi\Api4\CustomValue::get('Timesheet', FALSE)
      ->addWhere('id', '=', $id)
      ->execute()
      ->first();

    if ($entry) {
      $defaults['id'] = $entry['id'];
      $defaults['entity_id'] = $entry['entity_id'];
      $defaults['date_6'] = $entry['Date'];
      $defaults['from_7'] = $entry['From'];
      $defaults['to_8'] = $entry['To'];
      $defaults['description_9'] = $entry['Description'];
      $defaults['total_10'] = $entry['Total'];
      $defaults['total_decimal__11'] = $entry['Total_decimal_'];
      $defaults['hourly_fee_13'] = $entry['Hourly_Fee'];
      $defaults['total_amount_14'] = $entry['Total_Amount'];
    }

    return $defaults;
  }

  private function getNewEntry(): array {
    $defaults = [];
    $defaults['date_6'] = date('Y-m-d');

    $contactId = (int)CRM_Utils_Request::retrieveValue('cid', 'Integer', 0);
    if ($contactId) {
      $defaults['entity_id'] = $contactId;
    }

    if ($contactId == 8) {
      // etion
      $defaults['hourly_fee_13'] = 95.00;
    }
    else {
      // all other clients
      $defaults['hourly_fee_13'] = 110.00;
    }

    return $defaults;
  }

  private function getRenderableElementNames(): array {
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  private function getEntityRefFilterOnlyOrgs() {
    return [
      'api' => [
        'params' => ['contact_type' => 'Organization'],
      ],
    ];
  }

}
