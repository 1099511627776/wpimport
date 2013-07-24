{include file='header.tpl' noSidebar=true}
{include file='paging.tpl'}
<script type="text/javascript">
    $(function($){
        $('button.importpage').click(function(){
            $('#posts_table td.id').each(function(){
                createPost(aRouter['wpimport']+'posts/item'+this.dataset.id);
            });
            $('#posts_table td.id').each(function(){
                createPost(aRouter['wpimport']+'posts/comment'+this.dataset.id);
            });
        });
    });
</script>
    <button class="importpage button button-primary">Import Page</button>
    <button class="importcomment button button-primary">Import Comments</button>
    <table id="posts_table" class="import_table">
    {foreach from=$aPosts key=k item=v}
    <tr>
        <td class="id" data-id="{$k}">{$k}</td>
        <td>{$v.title}</td>
        <td>{$v.alias}</td>
        <td>{$v.created_by}</td>
        <td>
            {if $v.ls_status ne ""}
                {$v.ls_status}
            {else}
                    <a href="#" onclick="createPost('{router page='wpimport'}posts/item{$k}'); return false;">
                        {if $v.status eq "exists"}
                            Update
                        {else}
                            Create
                        {/if}
                    </a>
            {/if}
        </td>       
        <td>
            {if $v.status eq "exists"}
                <a href="#" onclick="createPost('{router page='wpimport'}posts/comment{$k}'); return false;">
                    Recreate comments
                </a>
            {/if}
        </td>       
    </tr>
    {/foreach}
    </table>