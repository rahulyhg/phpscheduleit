{include file='header.tpl' DisplayWelcome='false'}

<style type="text/css">
	@import url({$Path}css/reservation.css);
</style>

<a href="{$ReturnUrl}">&lt; {translate key="BackToCalendar"}</a><br/>
<form action="undefined.php" method="post">

<input type="submit" value="{translate key="Save"}" class="button"></input>
<input type="button" value="{translate key="Cancel"}" class="button" onclick="window.location='{$ReturnUrl}'"></input>

<table cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<div id="resourceNames" style="display:inline">{$ResourceName}</div>
			<a href="#" onclick="$('#dialogAddResources').dialog('open'); return false;">(Add more)</a>
		<td>
		<td>
		<a href="#">(Add Accessories)</a> // modal popup
		</td>
	</tr>
	<tr>
		<td><div id="additionalResources"></div></td>
		<td></td>
</table>

<div>
	{$UserName}
	<a href="#">(Add Participants)</a> // modal popup
	<a href="#">(Invite Guests)</a> // modal popup
</div>
<div>
{translate key='BeginDate'}
<input type="text" id="BeginDate" class="textbox" style="width:75px" value="{formatdate date=$StartDate}"/>
<select class="textbox" id="BeginPeriod" onchange="MaintainPeriodLength();">
	{foreach from=$Periods item=period}
		{if $period->IsReservable()}
			<option>{$period->Label()}</option>
		{/if}
	{/foreach}
</select>
{translate key='EndDate'}
<input type="text" id="EndDate" class="textbox" style="width:75px" value="{formatdate date=$EndDate}" />
<select class="textbox" id="EndPeriod">
	{foreach from=$Periods item=period}
		{if $period->IsReservable()}
			<option>{$period->Label()}</option>
		{/if}
	{/foreach}
</select>
</div>

<div>
	{translate key='Summary'}<br/>
	<textarea id="summary" class="expand50-200" cols="60"></textarea>
</div>

<div id="repeatDiv">
	Repeat: 
	<select id="repeatOptions" onchange="ChangeRepeatOptions(this)">
		<option value="none">Does Not Repeat</option>
		<option value="daily">Daily</option>
		<option value="weekly">Weekly</option>
		<option value="monthly">Monthly</option>
		<option value="yearly">Yearly</option>
	</select>
	<div id="repeatEveryDiv" style="display:none;" class="days weeks months years">
		Every: <select>{html_options options=$RepeatEveryOptions}</select>
		<span id="repeatEveryDaysText" class="days">days</span>
		<span id="repeatEveryMonthsText" class="weeks">weeks</span>
		<span id="repeatEveryWeeksText" class="months">months</span>
		<span id="repeatEveryYearsText" class="years">years</span>
	</div>
	<div id="repeatOnWeeklyDiv" style="display:none;" class="weeks">
		<input type="checkbox" id="repeatSun" />S
		<input type="checkbox" id="repeatMon" />M
		<input type="checkbox" id="repeatTue" />T
		<input type="checkbox" id="repeatWed" />W
		<input type="checkbox" id="repeatThu" />T
		<input type="checkbox" id="repeatFri" />F
		<input type="checkbox" id="repeatSat" />S
	</div>
	<div id="repeatOnMonthlyDiv" style="display:none;" class="months">
		<input type="radio" name="repeatMonthlyType" value="dayOfMonth" id="repeatMonthDay" checked="checked" /><label for="repeatMonthDay">day of month</label>
		<input type="radio" name="repeatMonthlyType" value="dayOfWeek" id="repeatMonthWeek" /><label for="repeatMonthWeek">day of week</label>
	</div>
	<div id="repeatUntilDiv" style="display:none;">
		Until: 
		<input type="text" id="EndRepeat" class="textbox" style="width:75px" value="{formatdate date=$StartDate}" />
	</div>
</div>

<input type="submit" value="{translate key="Save"}" class="button"></input>
<input type="button" value="{translate key="Cancel"}" class="button" onclick="window.location='{$ReturnUrl}'"></input>

<div id="dialogAddResources" title="Add Resources">
	<p>Some text that you want to display to the user.</p>
	{foreach from=$AvailableResources item=resource}
		<input type="checkbox" name="additionalResources[]" id="additionalResource{$resource->Id()}" value="{$resource->Id()}" /><label for="additionalResource{$resource->Id()}">{$resource->Name()}</label><br/>
	{/foreach}
	<button id="btnConfirmAddResources" onclick="$('#dialogAddResources').dialog('close')">Add Selected</button>
	<button id="btnClearAddResources">Cancel</button>
</div>

</form>

{control type="DatePickerSetupControl" ControlId="BeginDate" DefaultDate=$StartDate}
{control type="DatePickerSetupControl" ControlId="EndDate" DefaultDate=$EndDate}
{control type="DatePickerSetupControl" ControlId="EndRepeat" DefaultDate=$EndDate}

{literal}
<script type="text/javascript" src="scripts/js/jquery.textarea-expander.js"></script>
<script type="text/javascript" src="scripts/reservation.js"></script>


<script type="text/javascript">

function MaintainPeriodLength()
{
	alert('change end period');
}

function ChangeRepeatOptions(comboBox)
{
	if ($(comboBox).val() != 'none')
	{
		$('#repeatUntilDiv').show();
	}
	else
	{
		$('#repeatDiv div[id!=repeatOptions]').hide();
	}
	
	if ($(comboBox).val() == 'daily')
	{
		$('#repeatDiv .weeks').hide();
		$('#repeatDiv .months').hide();
		$('#repeatDiv .years').hide();
		
		$('#repeatDiv .days').show();	
	}
	
	if ($(comboBox).val() == 'weekly')
	{
		$('#repeatDiv .days').hide();
		$('#repeatDiv .months').hide();
		$('#repeatDiv .years').hide();
		
		$('#repeatDiv .weeks').show();	
	}
	
	if ($(comboBox).val() == 'monthly')
	{
		$('#repeatDiv .days').hide();
		$('#repeatDiv .weeks').hide();
		$('#repeatDiv .years').hide();
		
		$('#repeatDiv .months').show();	
	}
	
	if ($(comboBox).val() == 'yearly')
	{
		$('#repeatDiv .days').hide();
		$('#repeatDiv .weeks').hide();
		$('#repeatDiv .months').hide();
		
		$('#repeatDiv .years').show();	
	}
	
}

</script>


{/literal}

{include file='footer.tpl'}