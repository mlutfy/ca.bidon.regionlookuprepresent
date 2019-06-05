<?php

/**
 *
 */
function civicrm_api3_regionlookuprepresent_lookuprepbyname($params) {
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
  elseif (!empty($params['contact_id'])) {
    throw new Exception('TODO civicrm_api3_regionlookuprepresent_lookuprepbyname');
  }

  return civicrm_api3_create_success($result, $params, 'Regionlookuprepresent', 'Lookuprepbyname');
}
