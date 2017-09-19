<?php

class CRM_Regionlookuprepresent_BAO_Riding {

  /**
   *
   */
  static public function createFromRepresent($values, $contact_sub_type) {
    $contact_id = NULL;

    // Check if the Riding already exists.
    $result = civicrm_api3('Contact', 'get', [
      'contact_type' => 'Organization',
      'contact_sub_type' => $contact_sub_type,
      'organization_name' => $values['district_name'],
      'return.contact_id' => 1,
      'sequential' => 1,
    ]);

    if ($result['count']) {
      $contact_id = $result['values'][0]['contact_id'];
    }

    $contact_id = self::createFromRepresentContact($values, $contact_sub_type, $contact_id);

    self::createFromRepresentEmail($values, $contact_id);
    self::createFromRepresentWebsites($values, $contact_id);

    foreach ($values['offices'] as $office) {
      self::createFromRepresentAddress($office, $contact_id);
      self::createFromRepresentPhone($office, $contact_id);
    }
  }

  /**
   *
   */
  static public function createFromRepresentContact($values, $contact_sub_type, $contact_id = NULL) {
    $params = [];
    $params['contact_type'] = 'Organization';
    $params['contact_sub_type'] = $contact_sub_type;

    if ($contact_id) {
      $params['contact_id'] = $contact_id;
    }
    else {
      if (empty($values['district_name'])) {
        CRM_Core_Error::fatal('district_name is required when creating new Ridings.');
      }

      $params['organization_name'] = $values['district_name'];
    }

    // FIXME: hardcoded custom field
    $params['custom_66'] = $values['first_name'];
    $params['custom_67'] = $values['last_name'];
    $params['custom_68'] = $values['party_name'];
    $params['custom_69'] = $values['photo_url'];

    $result = civicrm_api3('Contact', 'create', $params);
    return $result['id'];
  }

  /**
   *
   */
  static public function createFromRepresentEmail($values, $contact_id) {
    $params = [];

    $result = civicrm_api3('Email', 'get', [
      'contact_id' => $contact_id,
      'location_type_id' => 8, // FIXME hardcoded. 8=legislature. MPs only have 1 official email.
      'sequential' => 1,
    ]);

    if ($result['count']) {
      $params['id'] = $result['values'][0]['id'];
    }

    $params['contact_id'] = $contact_id;
    $params['email'] = $values['email'];
    $params['location_type_id'] = 8; // FIXME

    civicrm_api3('Email', 'create', $params);
  }

  /**
   *
   */
  static public function createFromRepresentWebsites($values, $contact_id) {
    $params = [];

    // Main website (usually the offical government one, not a party/personal site).
    $result = civicrm_api3('Website', 'get', [
      'contact_id' => $contact_id,
      'website_type_id' => 1, // FIXME hardcoded. 1=main website
      'sequential' => 1,
    ]);

    if ($result['count']) {
      $params['id'] = $result['values'][0]['id'];
    }

    $params['contact_id'] = $contact_id;
    $params['url'] = $values['url'];
    $params['website_type_id'] = 1; // FIXME

    civicrm_api3('Website', 'create', $params);

    // Twitter
    $params = [];
    if (isset($values['extra']['twitter'])) {
      $result = civicrm_api3('Website', 'get', [
        'contact_id' => $contact_id,
        'website_type_id' => 11, // FIXME hardcoded. 11=twitter
        'sequential' => 1,
      ]);

      if ($result['count']) {
        $params['id'] = $result['values'][0]['id'];
      }

      $params['contact_id'] = $contact_id;
      $params['url'] = $values['extra']['twitter'];
      $params['website_type_id'] = 11; // FIXME twitter

      civicrm_api3('Website', 'create', $params);
    }

    // Personnal
    $params = [];
    if (!empty($values['personal_url'])) {
      $result = civicrm_api3('Website', 'get', [
        'contact_id' => $contact_id,
        'website_type_id' => 15, // FIXME website contact form
        'sequential' => 1,
      ]);

      if ($result['count']) {
        $params['id'] = $result['values'][0]['id'];
      }

      $params['contact_id'] = $contact_id;
      $params['url'] = $values['personal_url'];
      $params['website_type_id'] = 15; // FIXME website contact form

      civicrm_api3('Website', 'create', $params);
    }
  }

  /**
   *
   */
  static public function createFromRepresentAddress($values, $contact_id) {
    $map = [
      'legislature' => 8, // FIXME
      'constituency' => 9, // FIXME
    ];

    $params = [
      'contact_id' => $contact_id,
      'country_id' => 1039, // FIXME 1039=Canada
    ];

    if (empty($values['postal'])) {
      return;
    }

    if (isset($map[$values['type']])) {
      $params['location_type_id'] = $map[$values['type']];
    }

    if (empty($params['location_type_id'])) {
      CRM_Core_Error::fatal('Unknown location type: ' . $values['type']);
    }

    // Search for an existing address
    $result = civicrm_api3('Address', 'get', [
      'contact_id' => $contact_id,
      'location_type_id' => $params['location_type_id'],
      'sequential' => 1,
    ]);

    if ($result['count']) {
      $params['id'] = $result['values'][0]['id'];
    }

    $parts = explode("\n", $values['postal']);

    // FIXME: assuming the line 1 is the main address, line 2 is appt/unit, line 3 has comment, line 4 has city, province, postcode.
    // This seem normalized in the Federal data, but is it in other data sets?
    //
    // Example:
    // 886 Thornhill Street
    // (Main Office)
    // Unit E                                                  
    // Morden MB  R6M 2E1

    $params['street_address'] = array_shift($parts);

    while (count($parts) > 1) {
      // Ignore empty lines
      if (empty($parts[0])) {
        array_shift($parts);
        continue;
      }

      if (empty($params['supplemental_address_1'])) {
        $params['supplemental_address_1'] = array_shift($parts);
      }
      else {
        $params['supplemental_address_1'] .= ' ' . array_shift($parts);
      }
    }

    // Extract the postal code
    $last_line = array_shift($parts);

    if (preg_match('/ ([A-Z][0-9][A-Z] [0-9][A-Z][0-9])$/', $last_line, $matches)) {
      $params['postal_code'] = $matches[1];
      $last_line = preg_replace('/ [A-Z][0-9][A-Z] [0-9][A-Z][0-9]$/', '', $last_line);
      $last_line = trim($last_line);
    }

    // Extract the province
    static $province_abbreviations = NULL;

    if (empty($province_abbreviations)) {
      $province_abbreviations = CRM_Core_PseudoConstant::stateProvinceAbbreviation(NULL, TRUE);
    }

    if (preg_match('/ ([A-Z]{2})$/', $last_line, $matches)) {
      $params['state_province_id'] = array_search($matches[1], $province_abbreviations);
      $last_line = preg_replace('/ [A-Z]{2}$/', '', $last_line);
      $last_line = trim($last_line);
    }

    if (empty($params['state_province_id'])) {
      CRM_Core_Error::fatal('Unknown state/province: ' . $last_line . ' -- ' . print_r($values, 1));
    }

    // We assume that what is left is the city
    $params['city'] = $last_line;

    civicrm_api3('Address', 'create', $params);
  }

  /**
   *
   */
  static public function createFromRepresentPhone($values, $contact_id) {
    $map = [
      'legislature' => 8, // FIXME
      'constituency' => 9, // FIXME
    ];

    if (!empty($values['tel'])) {
      $params = [];

      if (isset($map[$values['type']])) {
        $params['location_type_id'] = $map[$values['type']];
      }

      if (empty($params['location_type_id'])) {
        CRM_Core_Error::fatal('Unknown location type: ' . $values['type']);
      }

      $result = civicrm_api3('Phone', 'get', [
        'contact_id' => $contact_id,
        'location_type_id' => $params['location_type_id'],
        'phone_type_id' => 1, // FIXME 1=phone
        'sequential' => 1,
      ]);

      if ($result['count']) {
        $params['id'] = $result['values'][0]['id'];
      }

      $params['contact_id'] = $contact_id;
      $params['phone'] = self::cleanupPhone($values['tel']);
      $params['phone_type_id'] = 1; // FIXME

      civicrm_api3('Phone', 'create', $params);
    }

    if (!empty($values['fax'])) {
      $params = [];

      if (isset($map[$values['type']])) {
        $params['location_type_id'] = $map[$values['type']];
      }

      if (empty($params['location_type_id'])) {
        CRM_Core_Error::fatal('Unknown location type: ' . $values['type']);
      }

      $result = civicrm_api3('Phone', 'get', [
        'contact_id' => $contact_id,
        'location_type_id' => $params['location_type_id'],
        'phone_type_id' => 3, // FIXME 3=phone
        'sequential' => 1,
      ]);

      if ($result['count']) {
        $params['id'] = $result['values'][0]['id'];
      }

      $params['contact_id'] = $contact_id;
      $params['phone'] = self::cleanupPhone($values['fax']);
      $params['phone_type_id'] = 3; // FIXME 3=fax

      civicrm_api3('Phone', 'create', $params);
    }

  }

  /**
   * Ex: 1 555 222-1212 becomes 555-222-1212
   */
  static public function cleanupPhone($phone) {
    $phone = preg_replace('/^1 /', '', $phone);
    $phone = str_replace('/ /', '-', $phone);

    return $phone;
  }

}
