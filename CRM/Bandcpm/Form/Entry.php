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

  public function postProcess(): void {
    $values = $this->exportValues();

    parent::postProcess();
  }

  private function addFormFields(): void {
    $shortFieldStyle = 'width: 5em';
    $longFieldStyle = 'width: 100%;';

    /*
      id
      entity_id
      date_6
      from_7
      to_8
      description_9
      total_10
      total_decimal__11
      hourly_fee_13
      total_amount_14
     */

    $this->add('text', 'id', 'Entry ID', ['style' => $shortFieldStyle, 'readonly' => TRUE]);
    $this->addEntityRef('entity_id', 'Client', $this->getEntityRefFilterOnlyOrgs(), TRUE);
    $this->add('datepicker', 'date_6', 'Date', [], TRUE, ['time' => FALSE]);
    $this->add('text', 'description_9', 'Description', ['style' => $longFieldStyle], TRUE);
    $this->add('text', 'from_7', 'From', ['style' => $shortFieldStyle], TRUE);
    $this->add('text', 'to_8', 'To', ['style' => $shortFieldStyle], TRUE);
    $this->add('text', 'total_10', 'Total (h)', ['style' => $shortFieldStyle, 'readonly' => TRUE], FALSE);
    $this->add('text', 'total_decimal__11', 'Total (decimal)', ['style' => $shortFieldStyle, 'readonly' => FALSE], FALSE);
    $this->addMoney('hourly_fee_13', 'Hourly fee', FALSE, ['style' => $shortFieldStyle, 'readonly' => FALSE]);
    $this->addMoney('total_amount_14', 'Total Amount', FALSE, ['style' => $shortFieldStyle, 'readonly' => FALSE]);
  }

  private function addFormButtons() {
    $this->addButtons([
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
      ]
    ];
  }

}
