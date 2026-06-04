<p><a class="crm-pager-link action-item crm-hover-button" href="{$previous}">&lt; Previous</a> <a class="crm-pager-link action-item crm-hover-button" href="{$next}">Next &gt;</a></p>
<h2>Month Total: {$monthTotal} EUR</h2>
<table>
  <tr>
    <th>Project</th>
    <th>Description</th>
    <th>Start Date</th>
    <th>Start Time</th>
    <th>End Time</th>
    <th>Duration (h)</th>
    <th>Duration (decimal)</th>
    <th>Billable Rate (EUR)</th>
    <th>Billable Amount (EUR)</th>
    <th>&nbsp;</th>
  </tr>
  {foreach from=$data item=entry}
  <tr>
    <td>{$entry.contact_name}</td>
    <td>{$entry.description}</td>
    <td>{$entry.date}</td>
    <td>{$entry.from}</td>
    <td>{$entry.to}</td>
    <td>{$entry.total}</td>
    {if $entry.contact_name eq ""}
      <td><strong>{$entry.total_decimal}</strong></td>
    {else}
      <td>{$entry.total_decimal}</td>
    {/if}

    <td>{$entry.hourly_fee}</td>

    {if $entry.contact_name eq ""}
      <td><strong>{$entry.total_amount}</strong></td>
    {else}
      <td>{$entry.total_amount}</td>
    {/if}

    {if $entry.hourly_fee lt 0}
      <td></td>
    {else}
      <td><a href="/civicrm/timesheet/add?id={$entry.id}">edit</a></td>
    {/if}
  </tr>
  {/foreach}
</table>
