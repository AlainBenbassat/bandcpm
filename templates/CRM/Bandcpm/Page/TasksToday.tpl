{foreach from=$taskGroups item=taskGroup}
  <h2 class="{$taskGroup->category}">{$taskGroup->title} <span class="numberOfItems">- {$taskGroup->numTasks} item(s)</span></h2>
  <table>
      {foreach from=$taskGroup->tasks item=task}
        <tr>
          <td>{$task->project}</td>
          <td width="70%"><a href="/civicrm/task#?Activity1={$task->id}">{$task->title}</a></td>
        </tr>
      {/foreach}
  </table>
{/foreach}

