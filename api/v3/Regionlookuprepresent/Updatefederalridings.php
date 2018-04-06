<?php

function civicrm_api3_regionlookuprepresent_updatefederalridings($params) {
  $options = array('http' => array('user_agent' => 'CiviCRM RegionLookupRepresent'));
  $context = stream_context_create($options);
  $result = file_get_contents('https://represent.opennorth.ca/representatives/house-of-commons/?limit=999', FALSE, $context);

  $contact_sub_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_ctype');

  if (empty($contact_sub_type_id)) {
    Civi::log()->debug('RegionlookupRepresent: updateFederalRidings skipped, since regionlookuprepresent_federalriding_ctype is not set.');
    return;
  }

  $contact_sub_type = civicrm_api3('ContactType', 'getvalue', [
    'id' => $contact_sub_type_id,
    'return' => 'name',
  ]);

  if (!empty($result)) {
    $data = json_decode($result, TRUE);

    foreach ($data['objects'] as $key => $values) {
      CRM_Regionlookuprepresent_BAO_Riding::createFromRepresent($values, $contact_sub_type);
    }
  }
}
