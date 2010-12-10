<div id="page-header">
  <div id="header-banner" > 
    <div id="banner-right"> 
	<p>{html_link href="javascript: help();" key="Help"}</p>
{if $LoggedIn eq 'true'} 
        <p> {html_link href="logout.php" key="Log Out"} </p>
{/if}
    </div>
    <div id="banner-image"><img src="{$Path}img/scheduleit-brand.png" alt="phpScheduleIt"/></div>
    <div id="banner-text"><h2>javascript fills this in...</h2></div>
    <div id="banner-welcome">
{if $LoggedIn eq 'true'} 
     <h4 class="welcomeBack">{translate key='Welcome Back' args=$UserName}</h4>
{/if}
    </div>
  </div>
{if $DisplayWelcome eq 'true'} 
  <div id="tabs">
    <p>
    <!-- now tabs row-->
	{foreach from=$Tabs item="tab"}
		<span id="{$tab.id}" onmouseover="
	                new Effect.Parallel( [
				new Effect.Morph('{$tab.id}', {literal}{style: {backgroundColor: '#bbb', borderBottomColor: '#bbb'}} {/literal}),
	   {foreach from=$tab.peers item='peer'}
				new Effect.Morph('{$peer}', {literal}{style: {backgroundColor: '#eee', borderBottomColor: '#000'}} {/literal}),
	                        new Effect.Fade('{$peer}SubTabs', {literal}{duration: 0.0}{/literal}), 
	   {/foreach}
	                        new Effect.Appear('{$tab.id}SubTabs')
	                ], {literal}{duration: 0.05} {/literal});
			return false;
		"
	  {if $tab.default}
	          class="tab" style="border-bottom-color: #bbb; background-color: #bbb"
	  {else}
	          class="tab" style="border-bottom-color: #000; background-color: #eee"
	  {/if}
	        >{$tab.text}</span>
	{/foreach}

        <!-- Need to find out a way to inject onmouseover event script into these links -->
        <span id="bookings-tab" class="tab">{html_link href="bookings.php" key="Bookings"}</span>
        <span id="dashboard-tab" class="tab">{html_link href="dashboard.php" key="MyDashboard"}</span>
      </p>
    </div>
    <div id="sub-tabs">
      <p>
		{foreach from=$Tabs item="tab"}
		  {if $tab.default} 
		        <div id="{$tab.id}SubTabs" class="subtab" style="display: block">
		  {else}
		        <div id="{$tab.id}SubTabs" class="subtab" style="display: none">
		  {/if} 
		  {foreach from=$tab.subtabs item="subtab"}
		          <a href="{$subtab.link}">{$subtab.text}</a> |
		  {/foreach}
			</div>
		{/foreach}
      </p>
  </div>
  {/if}
</div> <!--header--> 