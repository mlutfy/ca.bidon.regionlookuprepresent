<?php

function civicrm_api3_regionlookuprepresent_ridinglookup($params) {
  // FIXME: 3=Head office
  // FIXME lots of hardcoded stuff.
  $sql = 'SELECT c.id as contact_id, a.*, info.federal_riding_64 as federal_riding
    FROM civicrm_contact c
    INNER JOIN civicrm_address a ON (a.contact_id = c.id AND a.location_type_id = 3)
    LEFT JOIN civicrm_value_cmc_organisation_information_1 info ON (info.entity_id = c.id)
    WHERE c.contact_type = "Organization"
      AND c.contact_sub_type IS NULL
      AND info.federal_riding_64 IS NULL
      AND geo_code_1 IS NOT NULL';

  $dao = CRM_Core_DAO::executeQuery($sql);

  while ($dao->fetch()) {
    if ($dao->geo_code_1 && $dao->geo_code_2) {
      $geo = $dao->geo_code_1 . ',' . $dao->geo_code_2;
      $result = file_get_contents('https://represent.opennorth.ca/boundaries/?contains=' . $geo);
      $data = json_decode($result, TRUE);

      foreach ($data['objects'] as $boundary) {
        if ($boundary['boundary_set_name'] == 'Federal electoral district') {
          // I could not find an easy way to distinguish the result for a
          // pas boundary, vs current boundary, except by looking at the URL.
          // Ex: /boundaries/federal-electoral-districts/62001/
          // vs: /boundaries/federal-electoral-districts-2003-representation-order/46012/
          if (strpos($boundary['url'], '/boundaries/federal-electoral-districts/') === FALSE) {
            continue;
          }

          $result2 = civicrm_api3('Contact', 'get', [
            'contact_sub_type' => 'Federal_riding',
            'organization_name' => $boundary['name'],
            'sequential' => 1,
          ]);

          if ($result2['count']) {
            $riding_contact_id = $result2['values'][0]['contact_id'];

            civicrm_api3('Contact', 'create', [
              'contact_id' => $dao->contact_id,
              'custom_64' => $riding_contact_id,
            ]);

            Civi::log()->info($dao->contact_id . ': [' . $geo . '] Updated riding to ' . $riding_contact_id . ' (' . $boundary['name'] . ')');
          }
          else {
            Civi::log()->warning($dao->contact_id . ': [' . $geo . '] Could not find the riding for: ' . $boundary['name'] . ' -- ' . $boundary['url']);
          }
        }
      }

      sleep(1);
    }
    elseif ($dao->postal_code) {
      Civi::log()->warning($dao->contact_id . ': [' . $dao->postal_code . '] Address is not geocoded and postcode lookup not currently implemented.');
    }
  }
}
