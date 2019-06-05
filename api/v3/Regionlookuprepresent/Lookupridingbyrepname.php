<?php

/**
 * FIXME: duplicate 'lookuprepbynem'
 */
function civicrm_api3_regionlookuprepresent_lookupridingbyrepname($params) {
  if (!empty($params['name'])) {
    $name = $params['name'];

    $client = new GuzzleHttp\Client();
    $url = 'https://represent.opennorth.ca/representatives/?name=' . $params['name'];

    $response = $client->request('GET', $url, [
      'headers' => [
        'User-Agent' => 'CiviCRM RegionLookupRepresent',
      ],
    ]);

    $data = $response->getBody();
    $result = json_decode($data, TRUE);
  }

  return civicrm_api3_create_success($result, $params, 'Regionlookuprepresent', 'Lookupridingbypostcode');
}
