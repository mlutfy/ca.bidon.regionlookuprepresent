<?php

/**
 * Updates the riding information for contacts (organisations) that have
 * been already geolocated (based on their address), but not associated
 * with a riding.
 */
function civicrm_api3_regionlookuprepresent_batchridingupdatebygeo($params) {
  // Exclude 'riding' contact subtypes.
  // I'm not really sure why, to be honest. It's not a big deal?
  $excludes = [];
  if ($t = Civi::settings()->get('regionlookuprepresent_federalriding_ctype')) {
    $subtype_name = civicrm_api3('ContactType', 'getvalue', [
      'id' => $t,
      'return' => 'name',
    ]);
    $excludes[] = $subtype_name;
  }
  if ($t = Civi::settings()->get('regionlookuprepresent_provincialriding_ctype')) {
    $subtype_name = civicrm_api3('ContactType', 'getvalue', [
      'id' => $t,
      'return' => 'name',
    ]);
    $excludes[] = $subtype_name;
  }

  // Fetch the custom field for the federal riding
  $federalriding_cfid = Civi::settings()->get('regionlookuprepresent_federalriding_cfid');

  if (empty($federalriding_cfid)) {
    throw new Exception("The Federal Custom Field is not set. Please check the settings.");
  }

  $cf = civicrm_api3('CustomField', 'getsingle', [
    'id' => $federalriding_cfid,
    'api.CustomGroup.get' => [],
  ]);

  $federalriding_cf_table = $cf['api.CustomGroup.get']['values'][0]['table_name'];
  $federalriding_cf_field = $cf['column_name'];

  // Fetch the custom field for the provincial riding
  $provincialriding_cfid = Civi::settings()->get('regionlookuprepresent_provincialriding_cfid');

  if (empty($provincialriding_cfid)) {
    throw new Exception("The Federal Custom Field is not set. Please check the settings.");
  }

  $cf = civicrm_api3('CustomField', 'getsingle', [
    'id' => $provincialriding_cfid,
    'api.CustomGroup.get' => [],
  ]);

  $provincialriding_cf_table = $cf['api.CustomGroup.get']['values'][0]['table_name'];
  $provincialriding_cf_field = $cf['column_name'];

  // Location type for the address to lookup
  $contact_location_type_id = Civi::settings()->get('regionlookuprepresent_contact_loctype_id');

  if (empty($contact_location_type_id)) {
    throw new Exception("The Contact Location Type to lookup is not set. Please check the settings.");
  }

  $sql = 'SELECT c.id as contact_id, a.*, ' . $federalriding_cf_field . ' as federal_riding, prov.abbreviation
    FROM civicrm_contact c
    INNER JOIN civicrm_address a ON (a.contact_id = c.id AND a.location_type_id = ' . $contact_location_type_id . ')
    LEFT JOIN ' . $federalriding_cf_table . ' as info ON (info.entity_id = c.id)
      ' . ($federalriding_cf_table != $provincialriding_cf_table ? " LEFT JOIN $provincialriding_cf_table as info2 ON (info2.entity_id = c.id) " : '') . '
    LEFT JOIN civicrm_state_province prov ON (prov.id = a.state_province_id)
    WHERE c.contact_type = "Organization"
      ' . (!empty($excludes) ? ' AND (c.contact_sub_type NOT IN("' . implode('","', $excludes) . '") OR c.contact_sub_type IS NULL)' : '') . '
      AND (' . $federalriding_cf_field . ' IS NULL OR ' . $provincialriding_cf_field . ' IS NULL)
      AND geo_code_1 IS NOT NULL
      AND (street_address IS NOT NULL OR postal_code IS NOT NULL)
      AND c.is_deleted = 0';

  $sqlparams = [];

  // This is mostly for testing purposes
  if (!empty($params['contact_id'])) {
    $sql .= ' AND c.id = %1';
    $sqlparams[1] = [$params['contact_id'], 'Positive'];
  }

  $dao = CRM_Core_DAO::executeQuery($sql, $sqlparams);

  while ($dao->fetch()) {
    if ($dao->geo_code_1 && $dao->geo_code_2) {
      $geo = $dao->geo_code_1 . ',' . $dao->geo_code_2;
      $result = file_get_contents('https://represent.opennorth.ca/boundaries/?contains=' . $geo);
      $data = json_decode($result, TRUE);

      $found_federal = FALSE;
      $found_provincial = FALSE;

      foreach ($data['objects'] as $boundary) {
        if (!$found_federal && $boundary['boundary_set_name'] == 'Federal electoral district') {
          // I could not find an easy way to distinguish the result for a
          // pas boundary, vs current boundary, except by looking at the URL.
          // Ex: /boundaries/federal-electoral-districts/62001/
          // vs: /boundaries/federal-electoral-districts-2003-representation-order/46012/
          if (strpos($boundary['url'], '/boundaries/federal-electoral-districts/') === FALSE) {
            continue;
          }

          $found_federal = TRUE;

          $riding_contact_id = CRM_Regionlookuprepresent_BAO_Riding::findRiding($boundary['name'], 'Federal_riding');

          if ($riding_contact_id) {
            civicrm_api3('Contact', 'create', [
              'contact_id' => $dao->contact_id,
              'custom_' . $federalriding_cfid => $riding_contact_id,
            ]);

            Civi::log()->info($dao->contact_id . ': [' . $geo . '] Updated riding to ' . $riding_contact_id . ' (' . $boundary['name'] . ')');
          }
          else {
            Civi::log()->warning($dao->contact_id . ': [' . $geo . '] Could not find the federal riding for: ' . $boundary['name'] . ' -- ' . $boundary['url']);
          }
        }
        elseif (!$found_provincial && strpos($boundary['boundary_set_name'], 'electoral district')) {
          // Ex: Ontario electoral district
          // Here we don't have an easy way of finding the current boundary. We just assume the first result is the latest.
          $found_provincial = TRUE;

          $abbrev = $dao->abbreviation;

          // This is to match suffixes in Job/Regionlookuprepresentupdateprovincialridings.php
          if ($abbrev == 'PE') {
            $abbrev = 'PEI';
          }
          elseif ($abbrev == 'NL') {
            $abbrev = 'NFL';
          }

          $riding_contact_id = CRM_Regionlookuprepresent_BAO_Riding::findRiding($boundary['name'] . ' [' . $abbrev . ']', 'Provincial_riding');

          if ($riding_contact_id) {
            civicrm_api3('Contact', 'create', [
              'contact_id' => $dao->contact_id,
              'custom_' . $provincialriding_cfid => $riding_contact_id,
            ]);

            Civi::log()->info($dao->contact_id . ': [' . $geo . '] Updated riding to ' . $riding_contact_id . ' (' . $boundary['name'] . ')');
          }
          else {
            Civi::log()->warning($dao->contact_id . ': [' . $geo . '] Could not find the provincial riding for: ' . $boundary['name'] . ' [' . $abbrev . '] -- ' . $boundary['url']);
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
