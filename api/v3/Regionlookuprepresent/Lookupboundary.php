<?php

/**
 *
 */
function civicrm_api3_regionlookuprepresent_lookupboundary($params) {
  $client = new GuzzleHttp\Client();
  $url = 'https://represent.opennorth.ca/' . $params['url'];

  $response = $client->request('GET', $url, [
    'headers' => [
      'User-Agent' => 'CiviCRM RegionLookupRepresent',
    ],
  ]);

  $data = $response->getBody();
  $result = json_decode($data, TRUE);

  return civicrm_api3_create_success($result, $params, 'Regionlookuprepresent', 'Lookupboundary');
}
