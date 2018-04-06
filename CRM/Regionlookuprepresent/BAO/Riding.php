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
    self::createFromRepresentIndividual($values, $contact_id);

    $found_constituency_office = FALSE;

    foreach ($values['offices'] as $office) {
      // There might be multiple constituency offices, we only want the first one (main office).
      if ($office['type'] == 'constituency') {
        if ($found_constituency_office) {
          continue;
        }
        $found_constituency_office = TRUE;
      }

      self::createFromRepresentAddress($office, $contact_id);
      self::createFromRepresentPhone($office, $contact_id);
    }
  }

  /**
   * Creates the organisation record for the riding.
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
/*
    $params['custom_1'] = $values['first_name'];
    $params['custom_2'] = $values['last_name'];
    $params['custom_3'] = $values['party_name'];
    $params['custom_4'] = $values['photo_url'];
*/

    $result = civicrm_api3('Contact', 'create', $params);
    return $result['id'];
  }

  /**
   * Given an organisation (riding) contact_id, create/update the individual
   * record for the member of parliament.
   */
  static public function createFromRepresentIndividual($values, $contact_id) {
    // Fetch relationships of type "member of parliament"

    $relationship_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_mp_reltype');
    $contact_sub_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_mp_ctype');
    $custom_party = 'custom_' . Civi::settings()->get('regionlookuprepresent_party_field');

    if (empty($relationship_type_id) || empty($contact_sub_type_id)) {
      return;
    }

    $contact_sub_type = civicrm_api3('ContactType', 'getvalue', [
      'id' => $contact_sub_type_id,
      'return' => 'name',
    ]);

    $individual_id = NULL;

    $result = civicrm_api3('Relationship', 'get', [
      'contact_id_a' => $contact_id,
      'relationship_type_id' => $relationship_type_id,
      'is_active' => 1,
      'api.Contact.get' => [
        'id' => '$value.contact_id_b',
        'return.first_name' => 1,
        'return.last_name' => 1,
        'return.$custom_party' => 1,
      ],
    ]);

    $found_individual = FALSE;

    // It's unlikely that there are multiple related contacts,
    // but just in case, we will disable all non-matching relationships.
    foreach ($result['values'] as $rel) {
      $c = $rel['api.Contact.get']['values'][0];

      if ($c['first_name'] == $values['first_name'] && $c['last_name'] == $values['last_name']) {
        $found_individual = TRUE;
      }
      else {
        civicrm_api3('Relationship', 'create', [
          'id' => $rel['id'],
          'is_active' => 0,
          'end_date' => date('Y-m-d'),
        ]);
      }
    }

    if (!$found_individual) {
      self::createFromRepresentIndividualHelper([
        'riding_contact_id' => $contact_id,
        'contact_sub_type' => $contact_sub_type,
        'relationship_type_id' => $relationship_type_id,
        'first_name' => $values['first_name'],
        'last_name' => $values['last_name'],
        'party_name' => $values['party_name'],
        'photo_url' => $values['photo_url'],
      ]);
    }
  }

  /**
   *
   */
  static public function createFromRepresentIndividualHelper($params) {
    $custom_party = 'custom_' . Civi::settings()->get('regionlookuprepresent_party_field');

    $individual = civicrm_api3('Contact', 'create', [
      'contact_type' => 'Individual',
      'contact_sub_type' => $params['contact_sub_type'],
      'first_name' => $params['first_name'],
      'last_name' => $params['last_name'],
      "$custom_party" => $params['party_name'],
    ]);

    // FIXME/TODO: photo_url

    civicrm_api3('Relationship', 'create', [
      'relationship_type_id' => $params['relationship_type_id'],
      'contact_id_a' => $params['riding_contact_id'],
      'contact_id_b' => $individual['id'],
      'is_active' => 1,
      'start_date' => date('Y-m-d'),
    ]);
  }

  /**
   *
   */
  static public function createFromRepresentEmail($values, $contact_id) {
    $params = [];

    $location_type_id = Civi::settings()->get('regionlookuprepresent_email_loctype_id');

    if (empty($location_type_id)) {
      return;
    }

    $result = civicrm_api3('Email', 'get', [
      'contact_id' => $contact_id,
      'location_type_id' => $location_type_id,
      'sequential' => 1,
    ]);

    if ($result['count']) {
      $params['id'] = $result['values'][0]['id'];
    }

    if (empty($values['email'])) {
      return;
    }

    $params['contact_id'] = $contact_id;
    $params['email'] = $values['email'];
    $params['location_type_id'] = $location_type_id;

    // If there is no email, it might be a vacant riding.
    // Delete the old email, if there was one.
    if (empty($params['email'])) {
      if (!empty($params['id'])) {
        civicrm_api3('Email', 'delete', $params);
      }

      return;
    }

    civicrm_api3('Email', 'create', $params);
  }

  /**
   *
   */
  static public function createFromRepresentWebsites($values, $contact_id) {
    $params = [];

    $website_type_id = Civi::settings()->get('regionlookuprepresent_website_type_id');

    if (!empty($website_type_id)) {
      // Main website (usually the offical government one, not a party/personal site).
      $result = civicrm_api3('Website', 'get', [
        'contact_id' => $contact_id,
        'website_type_id' => $website_type_id,
        'sequential' => 1,
      ]);

      if ($result['count']) {
        $params['id'] = $result['values'][0]['id'];
      }

      $params['contact_id'] = $contact_id;
      $params['url'] = $values['url'];
      $params['website_type_id'] = $website_type_id;

      civicrm_api3('Website', 'create', $params);
    }

    // Twitter
    $twitter_type_id = Civi::settings()->get('regionlookuprepresent_twitter_type_id');

    if (!empty($twitter_type_id)) {
      $params = [];
      if (isset($values['extra']['twitter'])) {
        $result = civicrm_api3('Website', 'get', [
          'contact_id' => $contact_id,
          'website_type_id' => $twitter_type_id,
          'sequential' => 1,
        ]);

        if ($result['count']) {
          $params['id'] = $result['values'][0]['id'];
        }

        $params['contact_id'] = $contact_id;
        $params['url'] = $values['extra']['twitter'];
        $params['website_type_id'] = $twitter_type_id;

        civicrm_api3('Website', 'create', $params);
      }
    }

    // Personnal
    $personalwebsite_type_id = Civi::settings()->get('regionlookuprepresent_personalwebsite_type_id');

    if (!empty($personalwebsite_type_id)) {
      $params = [];
      if (!empty($values['personal_url'])) {
        $result = civicrm_api3('Website', 'get', [
          'contact_id' => $contact_id,
          'website_type_id' => $personalwebsite_type_id,
          'sequential' => 1,
        ]);

        if ($result['count']) {
          $params['id'] = $result['values'][0]['id'];
        }

        $params['contact_id'] = $contact_id;
        $params['url'] = $values['personal_url'];
        $params['website_type_id'] = $personalwebsite_type_id;

        civicrm_api3('Website', 'create', $params);
      }
    }
  }

  /**
   *
   */
  static public function createFromRepresentAddress($values, $contact_id) {
    $map = [
      'legislature' => Civi::settings()->get('regionlookuprepresent_legislature_loctype_id'),
      'constituency' => Civi::settings()->get('regionlookuprepresent_constituent_loctype_id'),
    ];

    $params = [
      'contact_id' => $contact_id,
      'country_id' => 1039, // FIXME 1039=Canada
    ];

    if (empty($values['postal'])) {
      return;
    }

    if (!empty($map[$values['type']])) {
      $params['location_type_id'] = $map[$values['type']];
    }
    else {
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

    // We only import the Main Office, so this is not relevant.
    $parts[0] = preg_replace('/\(Main Office\)/', '', $parts[0]);

    $params['street_address'] = array_shift($parts);

    while (count($parts) > 1) {
      $parts[0] = preg_replace('/\(Main Office\)/', '', $parts[0]);

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

    // Skip geolocation, because it can generate timeouts
    // There is another CiviCRM cron for this.
    $params['skip_geocode'] = TRUE;

    civicrm_api3('Address', 'create', $params);
  }

  /**
   *
   */
  static public function createFromRepresentPhone($values, $contact_id) {
    $map = [
      'legislature' => Civi::settings()->get('regionlookuprepresent_legislature_loctype_id'),
      'constituency' => Civi::settings()->get('regionlookuprepresent_constituent_loctype_id'),
    ];

    if (!empty($values['tel'])) {
      $params = [];

      if (!empty($map[$values['type']])) {
        $params['location_type_id'] = $map[$values['type']];
      }
      else {
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
        'phone_type_id' => 3, // FIXME 3=fax
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
