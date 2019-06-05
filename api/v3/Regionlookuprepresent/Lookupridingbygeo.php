<?php

/**
 *
 */
function civicrm_api3_regionlookuprepresent_lookupridingbygeo($params) {
  $client = new GuzzleHttp\Client();
  $url = 'https://represent.opennorth.ca/representatives/?point=' . $params['latitude'] . ',' . $params['longitude'];

  $response = $client->request('GET', $url, [
    'headers' => [
      'User-Agent' => 'CiviCRM RegionLookupRepresent',
    ],
  ]);

  $data = $response->getBody();
  $result = json_decode($data, TRUE);

  // Return the same structure as Lookupridingbypostcode
  $result['representatives_concordance'] = $result['objects'];
  $result['representatives_centroid'] = [];

  return civicrm_api3_create_success($result, $params, 'Regionlookuprepresent', 'Lookupridingbygeo');
}
