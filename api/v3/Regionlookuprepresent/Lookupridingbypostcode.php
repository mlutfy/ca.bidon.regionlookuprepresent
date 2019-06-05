<?php

/**
 *
 */
function civicrm_api3_regionlookuprepresent_lookupridingbypostcode($params) {
  $postcode = $params['postcode'];

  // Validate before sending to Represent
  if (!preg_match('/^[A-Z][0-9][A-Z][0-9][A-Z][0-9]$/', $postcode)) {
    throw new Exception("postcode must be in format A1A1A1");
  }

  $client = new GuzzleHttp\Client();
  $url = 'https://represent.opennorth.ca/postcodes/' . $postcode . '/';

  $response = $client->request('GET', $url, [
    'headers' => [
      'User-Agent' => 'CiviCRM RegionLookupRepresent',
    ],
  ]);

  $data = $response->getBody();
  $result = json_decode($data, TRUE);

  return civicrm_api3_create_success($result, $params, 'Regionlookuprepresent', 'Lookupridingbypostcode');
}
