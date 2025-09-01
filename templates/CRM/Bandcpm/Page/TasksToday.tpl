{foreach from=$taskGroups item=taskGroup}
  <h2 class="{$taskGroup->category}">{$taskGroup->title} <span class="numberOfItems">- {$taskGroup->numTasks} item(s)</span></h2>
  <div class="task-grid">
      {foreach from=$taskGroup->tasks item=task}
        <div class="task-grid__project">{$task->project}</div>
        <div class="task-grid__title"><a href="/civicrm/task#?Activity1={$task->id}">{$task->title}</a></div>
      {/foreach}
  </div>
{/foreach}

