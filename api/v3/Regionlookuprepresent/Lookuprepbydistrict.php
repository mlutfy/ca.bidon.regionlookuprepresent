<?php

/**
 *
 */
function civicrm_api3_regionlookuprepresent_lookuprepbydistrict($params) {
  $cfid_party = 'custom_' . Civi::settings()->get('regionlookuprepresent_party_field');
  $cfid_boundary = 'custom_' . Civi::settings()->get('regionlookuprepresent_boundary_url');
  $cfid_biography = 'custom_' . Civi::settings()->get('regionlookuprepresent_biography_field');
  $legislature_loctype_id = Civi::settings()->get('regionlookuprepresent_legislature_loctype_id');
  $constituent_loctype_id = Civi::settings()->get('regionlookuprepresent_constituent_loctype_id');

  $contact_subtype_setting = ($params['jurisdiction'] == 'provincial' ? 'regionlookuprepresent_provincialriding_ctype' : 'regionlookuprepresent_federalriding_ctype');
  $contact_sub_type = Civi::settings()->get($contact_subtype_setting);

  $fetch_from_rel_setting = ($params['jurisdiction'] == 'provincial' ? 'regionlookuprepresent_provincialriding_mp_reltype' : 'regionlookuprepresent_federalriding_mp_reltype');
  $fetch_from_rel = Civi::settings()->get($fetch_from_rel_setting);

  $subtype_name = civicrm_api3('ContactType', 'getvalue', [
    'id' => $contact_sub_type,
    'return' => 'name',
  ]);

  $api_params = [
    'contact_type' => 'Organization',
    'contact_sub_type' => $subtype_name,
    'is_deleted' => 0,
    'return' => ['id', 'organization_name', 'nick_name', 'image_URL', $cfid_party, $cfid_boundary, $cfid_biography],
    'sequential' => 1,
  ];

  if (!$fetch_from_rel) {
    $api_params['api.Phone.get'] = [];
    $api_params['api.Address.get'] = [
      'return' => ['location_type_id', 'street_address', 'supplemental_address_1', 'city', 'postal_code', 'state_province_id', 'country_id'],
      'api.StateProvince.getsingle' => [],
      'api.Country.getsingle' => [],
    ];
    $api_params['api.Email.get'] = ['sequential' => 1];
  }

  if (!empty($params['name'])) {
    $api_params['organization_name'] = [
      'LIKE' => $params['name'] . '%',
    ];
  }
  elseif (!empty($params['contact_id'])) {
    $api_params['contact_id'] = $params['contact_id'];
  }

  $result = civicrm_api3('Contact', 'get', $api_params);

  foreach ($result['values'] as $key => &$val) {
    $val['party_name'] = CRM_Utils_Array::value($cfid_party, $val);
    $val['boundary_url'] = CRM_Utils_Array::value($cfid_boundary, $val);

    if (Civi::settings()->get('regionlookuprepresent_federalriding_nickname')) {
      $val['name'] = $val['nick_name'];
    }
    else {
      // TODO
      $val['name'] = 'TODO';
    }

    // Make the addresses easier to find, use Represent terminology.
    if (!isset($val['offices'])) {
      $val['offices'] = [];
    }

    // If we are storing the data in the individual record, fetch from there instead.
    // Horrible code, cleanup one day.
    if ($fetch_from_rel) {
      $t = civicrm_api3('Relationship', 'get', [
        'relationship_type_id' => $fetch_from_rel,
        'contact_id_b' => $val['id'],
        'is_active' => 1,
        'sequential' => 1,
      ]);

      if (empty($t['values'][0])) {
        continue;
      }

      $t = civicrm_api3('Contact', 'get', [
        'id' => $t['values'][0]['contact_id_a'],
        'api.Phone.get' => [],
        'api.Address.get' => [
          'return' => ['location_type_id', 'street_address', 'supplemental_address_1', 'city', 'postal_code', 'state_province_id', 'country_id'],
          'api.StateProvince.getsingle' => [],
          'api.Country.getsingle' => [],
        ],
        'api.Email.get' => ['sequential' => 1],
        'return' => ['id', 'organization_name', 'nick_name', 'image_URL', $cfid_party, $cfid_boundary, $cfid_biography],
        'sequential' => 1,
      ]);

      if (empty($t['values'][0])) {
        continue;
      }

      $indiv = $t['values'][0];
      $val['api.Address.get'] = $indiv['api.Address.get'];
      $val['api.Email.get'] = $indiv['api.Email.get'];
      $val['api.Phone.get'] = $indiv['api.Phone.get'];
      $val['image_URL'] = $indiv['image_URL'];
      $val['party_name'] = CRM_Utils_Array::value($cfid_party, $indiv);
    }

    foreach ($val['api.Address.get']['values'] as $kk => &$vv) {
      if ($vv['location_type_id'] == $constituent_loctype_id) {
        $val['offices']['constituency'] = $vv;
        $val['offices']['constituency']['state_province_abbreviation'] = $vv['api.StateProvince.getsingle']['abbreviation'];
        $val['offices']['constituency']['state_province'] = $vv['api.StateProvince.getsingle']['name'];
        $val['offices']['constituency']['country_abbreviation'] = $vv['api.Country.getsingle']['iso_code'];
        $val['offices']['constituency']['country'] = $vv['api.Country.getsingle']['name'];
      }
      if ($vv['location_type_id'] == $legislature_loctype_id) {
        $val['offices']['legislature'] = $vv;
        $val['offices']['legislature']['state_province_abbreviation'] = $vv['api.StateProvince.getsingle']['abbreviation'];
        $val['offices']['legislature']['state_province'] = $vv['api.StateProvince.getsingle']['name'];
        $val['offices']['legislature']['country_abbreviation'] = $vv['api.Country.getsingle']['iso_code'];
        $val['offices']['legislature']['country'] = $vv['api.Country.getsingle']['name'];
      }
    }

    foreach ($val['api.Phone.get']['values'] as $kk => &$vv) {
      if (!isset($val['offices'])) {
        $val['offices'] = [];
      }

      if ($vv['location_type_id'] == $constituent_loctype_id) {
        $val['offices']['constituency']['phone'] = $vv['phone'];
      }
      if ($vv['location_type_id'] == $legislature_loctype_id) {
        $val['offices']['legislature']['phone'] = $vv['phone'];
      }
    }

    if (!empty($val['api.Email.get']['values'][0]['email'])) {
      $val['email'] = $val['api.Email.get']['values'][0]['email'];
    }
  }

  $data = [];

  return civicrm_api3_create_success($result['values'], $params, 'Regionlookuprepresent', 'Lookuprepbydistrict');
}
