<div>
  <h3>Federal Riding Settings</h3>
  {foreach from=$subsections.federalriding item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}
        <div class="description">{$elementDescriptions.$elementName}</div>
      </div>
      <div class="clear"></div>
    </div>
  {/foreach}
</div>

<div>
  <h3>Provincial Riding Settings</h3>
  {foreach from=$subsections.provincialriding item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}
        <div class="description">{$elementDescriptions.$elementName}</div>
      </div>
      <div class="clear"></div>
    </div>
  {/foreach}
</div>

<div>
  <h3>General Riding Location Settings</h3>
  {foreach from=$subsections.ridingsetting item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}
        <div class="description">{$elementDescriptions.$elementName}</div>
      </div>
      <div class="clear"></div>
    </div>
  {/foreach}
</div>

<div>
  <h3>Contact Settings</h3>
  {foreach from=$subsections.contactsetting item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}
        <div class="description">{$elementDescriptions.$elementName}</div>
      </div>
      <div class="clear"></div>
    </div>
  {/foreach}
</div>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
