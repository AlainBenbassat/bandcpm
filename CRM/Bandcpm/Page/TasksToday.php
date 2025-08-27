<?php
use CRM_Bandcpm_ExtensionUtil as E;

class CRM_Bandcpm_Page_TasksToday extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('Today'));
    Civi::resources()->addStyleFile('bandcpm', 'css/style.css');

    $fetcher = new CRM_Bandcpm_BAO_Fetcher();
    $taskGroups = $fetcher->getToday();
    $this->assign('taskGroups', $taskGroups);

    parent::run();
  }

}
