<?php

function civicrm_api3_regionlookuprepresent_updateprovincialridings($params) {
  // Pre-flight checks
  $contact_sub_type_id = Civi::settings()->get('regionlookuprepresent_provincialriding_ctype');

  if (empty($contact_sub_type_id)) {
    Civi::log()->warning('RegionlookupRepresent: updateProvincialRidings skipped, since regionlookuprepresent_provincialriding_ctype is not set.');
    return;
  }

  $contact_sub_type = civicrm_api3('ContactType', 'getvalue', [
    'id' => $contact_sub_type_id,
    'return' => 'name',
  ]);

  // FIXME: no data for Nunavut, Yukon, NWT?
  $province_sets = [
    'AB' => 'https://represent.opennorth.ca/representatives/alberta-legislature/?limit=999',
    'BC' => 'https://represent.opennorth.ca/representatives/bc-legislature/?limit=999',
    'MB' => 'https://represent.opennorth.ca/representatives/manitoba-legislature/?limit=999',
    'NB' => 'https://represent.opennorth.ca/representatives/new-brunswick-legislature/?limit=999',
    'NFL' => 'https://represent.opennorth.ca/representatives/newfoundland-labrador-legislature/?limit=999',
    'ON' => 'https://represent.opennorth.ca/representatives/ontario-legislature/?limit=999',
    'PEI' => 'https://represent.opennorth.ca/representatives/pei-legislature/?limit=999',
    'SK' => 'https://represent.opennorth.ca/representatives/saskatchewan-legislature/?limit=999',
    'NS' => 'https://represent.opennorth.ca/representatives/nova-scotia-legislature/?limit=999',
    'QC' => 'https://represent.opennorth.ca/representatives/quebec-assemblee-nationale/?limit=999',
  ];

  $client = new GuzzleHttp\Client();

  foreach ($province_sets as $suffix => $url) {
    // Allow running only a specific province
    if (!empty($params['province']) && $params['province'] != $suffix) {
      continue;
    }

    // FIXME: this is not a good hack and might mess up deduping?
    // maybe we should store the original name by other means?
    if ($suffix) {
      $suffix = ' [' . $suffix . ']';
    }

    $response = $client->request('GET', $url, [
      'headers' => [
        'User-Agent' => 'CiviCRM RegionLookupRepresent',
      ],
    ]);

    $data = $response->getBody();
    $data = json_decode($data, TRUE);

    if (empty($data)) {
      Civi::log()->warning("RegionlookupRepresent: updateProvincialRidings: received an empty response from $url");
      continue;
    }

    foreach ($data['objects'] as $key => $values) {
      CRM_Regionlookuprepresent_BAO_Riding::createFromRepresent($values, $contact_sub_type, $suffix);
    }
  }
}
