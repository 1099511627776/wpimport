{include file='header.tpl'}
<script 
	language="JavaScript" 
	type="text/javascript" 
	src="{$sTemplateWebPathPlugin}js/wpimport.js">	
</script>
	<table width=100%>
	<tr>
		<th>id</th>
		<th>name</th>
		<th>desc</th>
		<th>alias</th>
		<th>action</th>
	</tr>
	{foreach from=$aCats key=k item=v}
	<tr>
		<td>{$k}&nbsp;&nbsp;</td>
		<td>{$v.name}</td>
		<td>{$v.description}</td>
		<td>{$v.alias}</td>
		<td>
			{if $v.ls_status ne ""}
				{$v.ls_status}
			{else}
					<a href="#" onclick="createCat('{router page='wpimport'}categories/{$k}');">
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