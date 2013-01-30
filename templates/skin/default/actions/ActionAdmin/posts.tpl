{include file='header.tpl'}
{include file='paging.tpl'}
<script 
	language="JavaScript" 
	type="text/javascript" 
	src="{$sTemplateWebPathPlugin}js/wpimport.js">	
</script>
	<table>
	{foreach from=$aPosts key=k item=v}
	<tr>
		<td>{$k}&nbsp;&nbsp;</td>
		<td>{$v.title}</td>
		<td>{$v.alias}</td>
		<td>
			{if $v.ls_status ne ""}
				{$v.ls_status}
			{else}
					<a href="#" onclick="createPost('{router page='wpimport'}posts/item{$k}');">
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
				<a href="#" onclick="createPost('{router page='wpimport'}posts/comment{$k}');">
					Recreate comments
				</a>
			{/if}
		</td>		
	</tr>
	{/foreach}
	</table>