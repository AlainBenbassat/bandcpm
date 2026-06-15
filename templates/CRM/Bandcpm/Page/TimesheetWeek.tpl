<table>
  <tr>
    <th>Week</th>
    <th>Month</th>
    <th>Total</th>
  </tr>
    {foreach from=$data item=week}
      {assign var=style value='background-color:#FFFFFF;color:#000000;font-weight:normal'}
      {if $week.total gt 15}{assign var=style value='background-color:#A2F5B6;color:#000000;font-weight:bold'}{/if}
      {if $week.total gt 19}{assign var=style value='background-color:#1DF250;color:#000000;font-weight:bold'}{/if}
      <tr>
        <td>{$week.week}</td>
        <td>{$week.month}</td>
        <td style="{$style}">{$week.total}</td>
      </tr>
    {/foreach}
</table>