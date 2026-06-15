<?php
declare(strict_types = 1);

use CRM_Bandcpm_ExtensionUtil as E;

class CRM_Bandcpm_Page_TimesheetWeek extends CRM_Core_Page {

  public function run() {
    $year = (int)CRM_Utils_Request::retrieveValue('year', 'Integer', date('Y'));

    CRM_Utils_System::setTitle("Timesheet per Week - $year");

    $data = $this->getTimesheetPerWeek($year);
    $this->assign('data', $data);

    parent::run();
  }

  private function getTimesheetPerWeek($year) {
    $rows = [];

    $sql = "
      select 
        week(date_6) week, 
        month(date_6) month, 
        round(sum(total_decimal__11)) total
      from 
        civicrm_value_timesheet_3
      where
        year(date_6) = $year
      group by 
        week(date_6) 
      order by 
        week(date_6) desc 
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $rows[] = [
        'week' => $dao->week,
        'month' => $dao->month,
        'total' => $dao->total,
      ];
    }

    return $rows;
  }

}
