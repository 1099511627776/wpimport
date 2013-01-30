{include file='header.tpl'}
<script 
	language="JavaScript" 
	type="text/javascript" 
	src="{$sTemplateWebPathPlugin}js/wpimport.js">	
</script>
<div>
	<table>
	{foreach from=$aUsers key=k item=v}
	<tr>
		<td>{$k}&nbsp;&nbsp;</td>
		<td>{$v.name}</td>
		<td>{$v.login}</td>
		<td>{$v.email}</td>
		<td>{$v.username}</td>
		<td>
			{if $v.ls_status ne ""}
				{$v.ls_status}
			{else}
					<a href="#" onclick="createUser('{router page='wpimport'}users/{$k}');">
						{if $v.status eq "exists"}
							Update
						{else}
							Create
						{/if}
					</a>
			{/if}
		</td>		
	</tr>
	{/foreach}
	</table>
</div>