<table>
  <tr>
    <th>Week</th>
    <th>Month</th>
    <th>Total</th>
  </tr>
    {foreach from=$data item=week}
      <tr>
        <td>{$week.week}</td>
        <td>{$week.month}</td>
        <td>{$week.total}</td>
      </tr>
    {/foreach}
</table>