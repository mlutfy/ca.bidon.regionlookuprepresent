<?php

/**
 *
 */
function civicrm_api3_regionlookuprepresent_getlegislator($params) {
  // TODO $name = $params['name'];

  // This is a bit ridiculous, but since we store the ctype ID,
  // we later need to lookup the name.
  $contact_subtype_federal = Civi::settings()->get('regionlookuprepresent_federalriding_ctype');
  $contact_subtype_provincial = Civi::settings()->get('regionlookuprepresent_provincialriding_ctype');

  $subtype_ids = [];
  $subtype_names = [];

  if (!empty($params['elected_office'])) {
    // ex: provincial or federal (see above).
    $subtype_ids[] = Civi::settings()->get('regionlookuprepresent_' . $params['elected_office'] . 'riding_ctype');
  }
  else {
    $subtype_ids[] = $contact_subtype_federal;
    $subtype_ids[] = $contact_subtype_provincial;
  }

  foreach ($subtype_ids as $t) {
    try {
      $subtype_names[] = civicrm_api3('ContactType', 'getvalue', [
        'id' => $t,
        'return' => 'name',
      ]);
    }
    catch (Exception $e) {
      throw new Exception('Unknown elected_office');
    }
  }

  if (empty($subtype_names)) {
    throw new Exception('Unknown elected_office');
  }

  // FIXME: we would need separate select fields, one for federal, one for provincial.
  $result = civicrm_api3('Contact', 'get', [
    'contact_sub_type' => [
      'IN' => $subtype_names,
    ],
    'contact_type' => 'Organization',
    'is_deleted' => 0,
    'return' => ['id', 'nick_name', 'organization_name', 'contact_subtype'],
    'sequential' => 1,
    'option.limit' => 0,
  ]);

  return civicrm_api3_create_success($result['values'], $params, 'Regionlookuprepresent', 'Lookuprepbyname');
}
