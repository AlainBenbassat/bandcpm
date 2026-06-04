<?php
declare(strict_types = 1);

use CRM_Bandcpm_ExtensionUtil as E;

class CRM_Bandcpm_Page_Timesheet extends CRM_Core_Page {
  private $monthTotal = 0.00;

  public function run() {
    $month = (int)CRM_Utils_Request::retrieveValue('month', 'Integer', date('m'));
    $year = (int)CRM_Utils_Request::retrieveValue('year', 'Integer', date('Y'));

    $data = $this->getTimesheet($month, $year);

    CRM_Utils_System::setTitle("Timesheet $year-" . $this->addLeadningZero($month));

    $this->assign('data', $data);
    $this->assign('monthTotal', number_format($this->monthTotal, 2, ',', '.'));
    $this->assign('year', $year);
    $this->assign('month', $month);

    parent::run();
  }

  private function getTimesheet(int $month, int $year): mixed {
    $data = [];

    $this->validateYearAndMonth($month, $year);
    $daoContacts = $this->getCompaniesToInvoice($month, $year);
    while ($daoContacts->fetch()) {
      $entries = $this->getTimesheetForCompany($daoContacts->id, $month, $year);
      $this->addSum($entries);
      $this->addBlankLine($entries);
      $data = array_merge($data, $entries);
    }

    foreach ($data as &$entry) {
      if ($entry['hourly_fee'] != '' && $entry['hourly_fee'] != '&nbsp;') {
        $entry['hourly_fee'] = number_format((float)$entry['hourly_fee'], 2, ',', '.');
      }

      if ($entry['total_amount'] != '' && $entry['total_amount'] != '&nbsp;') {
        $entry['total_amount'] = number_format((float) $entry['total_amount'], 2, ',', '.');
        $entry['total_decimal'] = number_format((float) $entry['total_decimal'], 2, ',', '.');
      }
    }

    return $data;
  }

  private function validateYearAndMonth(int $month, int $year): void {
    if ($month <= 0 || $month > 12 || $year <= 2000 || $year > date('Y')) {
      throw new InvalidArgumentException('Invalid date range');
    }
  }

  private function getCompaniesToInvoice(int $month, int $year): mixed {
    $sql = "
      select 
        c.id, 
        c.display_name 
      from 
        civicrm_contact c 
      where 
        exists (
          select 
            * 
          from 
            civicrm_value_timesheet_3 t 
          where 
            year(t.date_6) = $year
          and
            month(t.date_6) = $month
          and
            t.entity_id = c.id
        )
      and
        c.is_deleted = 0
      order by
        sort_name
    ";
    return CRM_Core_DAO::executeQuery($sql);
  }

  private function getTimesheetForCompany($id, $month, $year) {
    $rows = [];

    $sql = "
      select 
        t.id,
        c.id contact_id,
        c.display_name,
        DATE_FORMAT(t.date_6, '%Y-%m-%d') date_6,
        t.from_7,
        t.to_8,         
        t.description_9,    
        t.total_10,         
        t.total_decimal__11,
        t.hourly_fee_13,    
        t.total_amount_14  
      from 
        civicrm_value_timesheet_3 t 
      inner join
        civicrm_contact c on c.id = t.entity_id
      where
        c.id = $id
      and      
        year(t.date_6) = $year
      and
        month(t.date_6) = $month
      and
        c.is_deleted = 0  
      order by
        c.sort_name,
        t.date_6,
        t.from_7  
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $rows[] = [
        'id' => $dao->id,
        'contact_id' => $dao->contact_id,
        'contact_name' => $dao->display_name,
        'date' => $dao->date_6,
        'from' => $dao->from_7,
        'to' => $dao->to_8,
        'description' => $dao->description_9,
        'total' => $dao->total_10,
        'total_decimal' => $dao->total_decimal__11,
        'hourly_fee' => $dao->hourly_fee_13,
        'total_amount' => $dao->total_amount_14,
      ];
    }

    return $rows;
  }

  private function addSum(&$entries) {
    $totalInHours = 0.00;
    $totalAmount = 0.00;

    foreach ($entries as &$entry) {
      $totalInHours += $entry['total_decimal'];
      $totalAmount += $entry['total_amount'];
    }

    $entries[] = [
      'id' => '',
      'contact_id' => '',
      'contact_name' => '',
      'date' => '',
      'from' => '',
      'to' => '',
      'description' => '',
      'total' => '',
      'total_decimal' => $totalInHours,
      'hourly_fee' => '',
      'total_amount' => $totalAmount,
    ];

    $this->monthTotal += $totalAmount;
  }

  private function addBlankLine(&$entries) {
    $entries[] = array_fill_keys(array_keys($entries[0]), '&nbsp;');
  }

  private function addLeadningZero($i) {
    if ($i <= 9) {
      return '0' . $i;
    }
    return $i;
  }

}
