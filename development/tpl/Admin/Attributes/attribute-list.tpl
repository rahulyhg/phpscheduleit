{*
Copyright 2012 Nick Korbel

This file is part of phpScheduleIt.

phpScheduleIt is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

phpScheduleIt is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with phpScheduleIt.  If not, see <http://www.gnu.org/licenses/>.
*}

<h3>{$Attributes|count} {translate key=Attributes}</h3>
{if $Attributes|count > 0}
<table class="list">
	<tr>
		<th>{translate key=DisplayLabel}</th>
		<th>{translate key=Type}</th>
		<th>{translate key=Required}</th>
		<th>{translate key=ValidationExpression}</th>
		<th>{translate key=PossibleValues}</th>
	</tr>
	{foreach from=$Attributes item=attribute}
		{cycle values='row0,row1' assign=rowCss}
		<tr class="{$rowCss} editable" attributeId="{$attribute->Id()}">
			<td>{$attribute->Label()}</td>
			<td>{translate key=$Types[$attribute->Type()]}</td>
			<td>{if $attribute->Required()}
				{translate key=Yes}
				{else}
				{translate key=No}
			{/if}</td>
			<td>{$attribute->Regex()}</td>
			<td>{$attribute->PossibleValues()}</td>
		</tr>
	{/foreach}
</table>
{/if}

<script type="text/javascript">
	var attributeList = [];

	{foreach from=$Attributes item=attribute}
		attributeList.push({$attribute->Id()});
	{/foreach}

	$.data($('table.list'), 'list', attributeList);

	alert($.data($('table.list'), 'list'));
</script>