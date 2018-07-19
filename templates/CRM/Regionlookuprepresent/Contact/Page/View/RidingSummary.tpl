{if $ridingSummary}
  <div class="crm-regionlookuprepresent-contact-ridingsummary">
    {$ridingSummary.elected_official_name} {if $ridingSummary.elected_official_party}({$ridingSummary.elected_official_party}){/if}
  </div>

  {literal}
    <script>
      CRM.$('.crm-regionlookuprepresent-contact-ridingsummary').appendTo('#crm-contactname-content .crm-summary-display_name');
    </script>

    <style>
      #crm-container div.crm-summary-display_name {
        font-size: 22px;
        padding-top: 1em;
      }
      #crm-container div.crm-summary-display_name .crm-regionlookuprepresent-contact-ridingsummary {
        font-size: 14px;
        padding-top: 0.5em;
        padding-left: 20px; /* clear the icon */
      }
    </style>
  {/literal}
{/if}
