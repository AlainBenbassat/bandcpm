<?php

class CRM_Bandcpm_BAO_Fetcher {
  public function getBacklog() {
    $sql = "
      select
        c.id, c.display_name
      from
        civicrm_contact c
      inner join
        civicrm_activity_contact ac on ac.contact_id = c.id and ac.record_type_id <> 2
      inner join
        civicrm_activity a on ac.activity_id = a.id         
      where
        a.status_id not in (2, 3)
      group by
        c.id
      order by
        c.sort_name
    ";
    $orgDao = CRM_Core_DAO::executeQuery($sql);

    $taskGroups = [];
    while ($orgDao->fetch()) {
      $tg = new CRM_Bandcpm_BAO_TaskGroup();
      $tg->title = $orgDao->display_name;
      $tg->category = 'dueDate';
      $tg->tasks = $this->getTasksByTargetId($orgDao->id);
      $tg->numTasks = count($tg->tasks);

      $taskGroups[] = $tg;
    }

    return $taskGroups;
  }

  public function getPlanning() {
    $taskGroups = [];
    foreach (['Today', 'This week', 'Next week', 'This month', 'Next month', 'Unscheduled'] as $dueDate) {
      $tg = new CRM_Bandcpm_BAO_TaskGroup();
      $tg->title = $dueDate;
      $tg->category = 'dueDate';
      $tg->tasks = $this->getTasksByDueDate($dueDate);
      $tg->numTasks = count($tg->tasks);

      $taskGroups[] = $tg;
    }

    return $taskGroups;
  }

  public function getToday() {
    $taskGroups = [];
    foreach (['Now', 'Later', 'Maybe'] as $whenToday) {
      $tg = new CRM_Bandcpm_BAO_TaskGroup();
      $tg->title = $whenToday;
      $tg->category = strtolower($whenToday);
      $tg->tasks = $this->getTasksBywhenToday($whenToday);
      $tg->numTasks = count($tg->tasks);

      $taskGroups[] = $tg;
    }

    return $taskGroups;
  }

  private function getTasksByTargetId($targetId) {
    $activities = \Civi\Api4\Activity::get(FALSE)
      ->addSelect('id', 'subject', 'details', 'status_id:label', 'Time_Management.When_today')
      ->addWhere('target_contact_id', '=', $targetId)
      ->addWhere('status_id', 'NOT IN', [2, 3])
      ->execute();

    $tasks = [];
    foreach ($activities as $activity) {
      $task = new CRM_Bandcpm_BAO_Task();
      $task->id = $activity['id'];
      $task->title = $activity['subject'];
      $task->planning = $activity['status_id:label'];
      $task->whenToday = $activity['Time_Management.When_today'];

      $tasks[] = $task;
    }

    return $tasks;
  }

  private function getTasksByDueDate($dueDate) {
    $activities = \Civi\Api4\Activity::get(FALSE)
      ->addSelect('id', 'contact.display_name', 'subject', 'details', 'status_id:label', 'Time_Management.When_today')
      ->addJoin('Contact AS contact', 'LEFT', ['target_contact_id', '=', 'contact.id'])
      ->addWhere('status_id:label', '=', $dueDate)
      ->addOrderBy('contact.sort_name', 'ASC')
      ->execute();

    $tasks = [];
    foreach ($activities as $activity) {
      $task = new CRM_Bandcpm_BAO_Task();
      $task->id = $activity['id'];
      $task->project = $activity['contact.display_name'];
      $task->title = $activity['subject'];
      $task->planning = $activity['status_id:label'];
      $task->whenToday = $activity['Time_Management.When_today'];

      $tasks[] = $task;
    }

    return $tasks;
  }

  private function getTasksByWhenToday($whenToday) {
    $activities = \Civi\Api4\Activity::get(FALSE)
      ->addSelect('id', 'contact.display_name', 'subject', 'details', 'status_id:label', 'Time_Management.When_today')
      ->addJoin('Contact AS contact', 'LEFT', ['target_contact_id', '=', 'contact.id'])
      ->addWhere('status_id:label', '=', 'Today')
      ->addWhere('Time_Management.When_today:label', '=', $whenToday)
      ->addOrderBy('contact.sort_name', 'ASC')
      ->execute();

    $tasks = [];
    foreach ($activities as $activity) {
      $task = new CRM_Bandcpm_BAO_Task();
      $task->id = $activity['id'];
      $task->project = $activity['contact.display_name'];
      $task->title = $activity['subject'];
      $task->planning = $activity['status_id:label'];
      $task->whenToday = $activity['Time_Management.When_today'];

      $tasks[] = $task;
    }

    return $tasks;
  }
}

