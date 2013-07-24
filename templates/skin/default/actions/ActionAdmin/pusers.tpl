{include file="header.tpl"}
{if count($aUsers)>0}
    <table>
    {foreach from=$aUsers key=k item=v}
    <tr>
        <td>{$k}&nbsp;&nbsp;</td>
        <td>{$v.name}</td>
        <td>{$v.login}</td>
        <td>{$v.email}</td>
        <td>{$v.username}</td>
        <td>
            <a href="#" onclick="createUser('{router page='wpimport'}users/{$k}'); return false;">
                {if $v.ls_status eq "exists"}
                    Update
                {else}
                    Create
                {/if}
            </a>
        </td>       
    </tr>
    {/foreach}
    </table>
{/if}
{include file="paging.tpl" aPaging=$aPaging}
{include file="footer.tpl"}
