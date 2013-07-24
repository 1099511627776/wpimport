{include file="header.tpl"}
{debug}
{if count($aPages)>0}
    <table>
    {foreach from=$aPages key=k item=v}
    <tr>
        <td>{$k}&nbsp;&nbsp;</td>
        <td>{$v.id}</td>
        <td>{$v.title}</td>
        <td>{$v.created_by}</td>
        <td>
            <a href="#" onclick="createPost('{router page='wpimport'}pages/{$k}'); return false;">Import</a>
        </td>
    </tr>
    {/foreach}
    </table>
{/if}
{include file="paging.tpl" aPaging=$aPaging}
{include file="footer.tpl"}
