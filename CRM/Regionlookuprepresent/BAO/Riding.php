<?php

class CRM_Regionlookuprepresent_BAO_Riding {

  /**
   * States/provinces abbreviations
   * @var array
   */
  private static $province_abbreviations = [];

  /**
   *
   */
  static public function createFromRepresent($values, $contact_sub_type, $suffix = '') {
    // Check if the Riding already exists (and later update if necessary).
    $contact_id = self::findRiding($values['district_name'] . $suffix, $contact_sub_type);

    $contact_id = self::createFromRepresentContact($values, $contact_sub_type, $contact_id, $suffix);
//if ($contact_id != 144) { return; }

    //if (Civi::settings()->get('regionlookuprepresent_federalriding_nickname')) {
      //self::saveImageToDisk($values['photo_url'], $contact_id);
    //}

    $contact_sub_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_mp_ctype');

    if ($suffix != '') {
      //Provincial
      $mp_relationship_type_id = Civi::settings()->get('regionlookuprepresent_provincialriding_mp_reltype');
      $candidate_relationship_type_id = Civi::settings()->get('regionlookuprepresent_provincialriding_candidate_reltype');
    }
    else {
      //Federal
      $mp_relationship_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_mp_reltype');
      $candidate_relationship_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_candidate_reltype');
    }

    if (empty($mp_relationship_type_id) || empty($contact_sub_type_id)) {
      $contact_id_for_location_and_custom_fields = $contact_id;
      if (Civi::settings()->get('regionlookuprepresent_federalriding_nickname')) {
        self::createFromRepresentNickname($values, $contact_id_for_location_and_custom_fields,$suffix,'riding');
      }
    }	
    else {
      $contact_id_for_location_and_custom_fields = self::createFromRepresentIndividual($values, $contact_id, $mp_relationship_type_id, $contact_sub_type_id, $candidate_relationship_type_id);

      if ($suffix != '') {

        $province_abbreviation = trim($suffix," []");

        if ($province_abbreviation == 'NFL') {
          $province_abbreviation = 'NL';
        }
        else if ($province_abbreviation == 'PEI') {
          $province_abbreviation = 'PE';
        }

        $province = civicrm_api3('StateProvince', 'getsingle', [
          'sequential' => 1,
          'return' => ["id"],
          'country_id' => 1039, //Canada
          'abbreviation' => $province_abbreviation,
        ]);

        $result = civicrm_api3('Address', 'create', [
          'contact_id' => $contact_id_for_location_and_custom_fields,
          'location_type_id' => "Constituency",
          'state_province_id' => $province['id'],
        ]);

      }

      if (Civi::settings()->get('regionlookuprepresent_federalriding_nickname')) {
        self::createFromRepresentNickname($values, $contact_id_for_location_and_custom_fields,$suffix,'individual');
      }
    }	

    self::createFromRepresentPhoto($values, $contact_id_for_location_and_custom_fields);
    self::createFromRepresentParty($values, $contact_id_for_location_and_custom_fields);

    self::createFromRepresentEmail($values, $contact_id_for_location_and_custom_fields);
    self::createFromRepresentWebsites($values, $contact_id_for_location_and_custom_fields);

    $found_constituency_office = FALSE;

    foreach ($values['offices'] as $office) {
      // There might be multiple constituency offices, we only want the first one (main office).
      if ($office['type'] == 'constituency') {
        if ($found_constituency_office) {
          continue;
        }
        $found_constituency_office = TRUE;
      }

      self::createFromRepresentAddress($values, $office, $contact_id_for_location_and_custom_fields);
      self::createFromRepresentPhone($values, $office, $contact_id_for_location_and_custom_fields);
    }
  }

  /**
   * Creates the organisation record for the riding.
   */
  static public function createFromRepresentContact($values, $contact_sub_type, $contact_id = NULL, $suffix = NULL) {
    $params = [];
    $params['contact_type'] = 'Organization';
    $params['contact_sub_type'] = $contact_sub_type;

    if ($contact_id) {
      $params['contact_id'] = $contact_id;
    }
    else {
      if (empty($values['district_name'])) {
        throw new Exception('district_name is required when creating new Ridings.');
      }

      // FIXME: the suffix is a hack for provinces
      $params['organization_name'] = $values['district_name'] . $suffix;
    }

 //Moved to createFromRepresentNicknameForOrganization function
    //if (Civi::settings()->get('regionlookuprepresent_federalriding_nickname')) {
      //$params['nick_name'] = $values['first_name'] . ' ' . $values['last_name'];

 //Moved to createFromRepresentParty function
      //if ($id = Civi::settings()->get('regionlookuprepresent_party_field')) {
        //$cf = 'custom_' . $id;
        //$params[$cf] = $values['party_name'];
      //}

      if ($id = Civi::settings()->get('regionlookuprepresent_boundary_url')) {
        $cf = 'custom_' . $id;
        // Represent returns a relative URL, ex: /boundaries/foo
        $params[$cf] = 'https://represent.opennorth.ca' . $values['related']['boundary_url'];
      }
    //}

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
  static public function createFromRepresentIndividual(&$values, $contact_id, $mp_relationship_type_id, $contact_sub_type_id, $candidate_relationship_type_id) {

    //$relationship_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_mp_reltype');
    //$contact_sub_type_id = Civi::settings()->get('regionlookuprepresent_federalriding_mp_ctype');
    //$custom_party = 'custom_' . Civi::settings()->get('regionlookuprepresent_party_field');

/* no longer necessary because this function is called only if relationship_type and contact_sub_type are set
    if (empty($relationship_type_id) || empty($contact_sub_type_id)) {
      return;
    }
*/

    $contact_sub_type = civicrm_api3('ContactType', 'getvalue', [
      'id' => $contact_sub_type_id,
      'return' => 'name',
    ]);

    $mp_contact_id = NULL;

    // Fetch active relationships of type "member of parliament"
    // To look for mp among mps actively related to the riding (there should be only one)

    $mp_result = civicrm_api3('Relationship', 'get', [
      'contact_id_b' => $contact_id,
      'relationship_type_id' => $mp_relationship_type_id,
      'is_active' => 1,
      'api.Contact.get' => [
        'id' => '$value.contact_id_a',
	// add is not deleted?
        'return.first_name' => 1,
        'return.last_name' => 1,
        //'return.image_URL' => 1,
        //"return.$custom_party" => 1,
      ],
    ]);

    $found_mp = FALSE;

    foreach ($mp_result['values'] as $rel) {

      $mp = $rel['api.Contact.get']['values'][0]; // the related contact

      if ($mp['first_name'] == $values['first_name'] && $mp['last_name'] == $values['last_name']) {
        $found_mp = TRUE;
        $mp_contact_id = $mp['id'];

/* Moved to createFromRepresentPhoto function

        // Check if the photo was updated (new photo when they get re-elected)
        $photo_url = self::getConvertedImageFilename($values['photo_url'], $c['id']);

        if ($c['image_URL'] != $photo_url) {
          $photo_url = self::saveImageToDisk($values['photo_url'], $c['id']);
        }

        // FIXME/TODO: check if the party has changed.
*/

      }

      // It's unlikely that there are multiple related contacts,
      // but just in case, we will disable all non-matching relationships.

      else {
	// if db first and last names do not match open north first and last names
	// it means there has been a change of mp
	// so disable the relationship
        $relationship_id = civicrm_api3('Relationship', 'create', [
          'id' => $rel['id'],
          'is_active' => 0,
          'end_date' => date('Y-m-d'),
        ]);
      }
    }

    if (!$found_mp && !empty($candidate_relationship_type_id)) { //if mp hasn't been re-elected and we are working with candidates

      //Look for mp among candidates related to the riding
      //get all active candidate relationships to the riding
      //and get related contacts (there should be only one)
      $candidate_result = civicrm_api3('Relationship', 'get', [
        'contact_id_b' => $contact_id,
        'relationship_type_id' => $candidate_relationship_type_id,
        'is_active' => 1,
        'api.Contact.get' => [
          'id' => '$value.contact_id_a',
          'return.first_name' => 1,
          'return.last_name' => 1,
        ],
      ]);

      foreach ($candidate_result['values'] as $rel) {
	// If we are here in the code it's because there has been a change of mp
	// Otherwise mp would have been found with mp relationship
	// If there has been a change of mp it means the election is passed

        // The related contact
        $candidate = $rel['api.Contact.get']['values'][0];

        if ($candidate['first_name'] == $values['first_name'] && $candidate['last_name'] == $values['last_name']) {
          $found_mp = TRUE;
	  $mp_contact_id = $candidate['id'];

          // Disable the candidate relationship
          civicrm_api3('Relationship', 'create', [
            'id' => $rel['id'],
            'is_active' => 0,
            'end_date' => date('Y-m-d'),
          ]);


	  // Create an mp relationship
          $relationship_id = civicrm_api3('Relationship', 'create', [
            'relationship_type_id' => $mp_relationship_type_id,
            'contact_id_a' => $mp_contact_id,			
            'contact_id_b' => $contact_id,
            'start_date' => date('Y-m-d'),
          ]);
        }
        else {
	  // if candidate first and last names do not match open north first and last names
	  // it means they weren't elected
	  // so disable the candidate relationship
          civicrm_api3('Relationship', 'create', [
            'id' => $rel['id'],
            'is_active' => 0,
            'end_date' => date('Y-m-d'),
          ]);
        }
      }
    }

    if (!$found_mp) { //if mp isn't related to riding
      $mp_contact_id = self::createFromRepresentIndividualHelper([
        'riding_contact_id' => $contact_id,
        'contact_sub_type' => $contact_sub_type,
        'relationship_type_id' => $mp_relationship_type_id,
        'first_name' => $values['first_name'],
        'last_name' => $values['last_name'],
        //'party_name' => $values['party_name'],
        //'photo_url' => $values['photo_url'],
      ]);
    }
    return $mp_contact_id;

  }

  /**
   *
   */
  static public function createFromRepresentIndividualHelper($params) {
    //$custom_party = 'custom_' . Civi::settings()->get('regionlookuprepresent_party_field');

    $individual = civicrm_api3('Contact', 'create', [
      'contact_type' => 'Individual',
      'contact_sub_type' => $params['contact_sub_type'],
      'first_name' => $params['first_name'],
      'last_name' => $params['last_name'],
      //"$custom_party" => $params['party_name'],
    ]);

    //self::saveImageToDisk($params['photo_url'], $individual['id']);

    $relationship = civicrm_api3('Relationship', 'create', [
      'relationship_type_id' => $params['relationship_type_id'],
      'contact_id_b' => $params['riding_contact_id'],
      'contact_id_a' => $individual['id'],
      'is_active' => 1,
      'start_date' => date('Y-m-d'),
    ]);

    return $individual['id'];
  }

  /**
   *
   */
  static public function createFromRepresentPhoto(&$values, $contact_id) {

    // Check if the photo was updated (new photo when they get re-elected)
    $photo_url = self::getConvertedImageFilename($values['photo_url'], $contact_id);    	

    $result = civicrm_api3('Contact', 'getvalue', [
      'return' => "image_URL",
      'id' => $contact_id,
    ]);
	
    if ($result != $photo_url) {
      $photo_url = self::saveImageToDisk($values['photo_url'], $contact_id);
    }	
	
  }

  /**
   *
   */
  static public function createFromRepresentParty($values, $contact_id) {
    if ($id = Civi::settings()->get('regionlookuprepresent_party_field')) {
      $cf = 'custom_' . $id;
      $params[$cf] = $values['party_name'];
      $params['id'] = $contact_id;
      $result = civicrm_api3('Contact', 'create', $params);
    }
  }

  /**
   *
   */
  static public function createFromRepresentNickname($values, $contact_id, $suffix, $ctype) {
    $params['id'] = $contact_id;

    if ($ctype == 'individual') {
      $params['nick_name'] = $values['district_name'] . $suffix;
    }
    else if ($ctype == 'riding') {
      $params['nick_name'] = $values['first_name'] . ' ' . $values['last_name'];
    }

    civicrm_api3('Contact', 'create', $params);
  }

  /**
   *
   */
  static public function createFromRepresentEmail(&$values, $contact_id) {
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
  static public function createFromRepresentAddress(&$values, $office, $contact_id) {
    $map = [
      'legislature' => Civi::settings()->get('regionlookuprepresent_legislature_loctype_id'),
      'constituency' => Civi::settings()->get('regionlookuprepresent_constituent_loctype_id'),
    ];

    $params = [
      'contact_id' => $contact_id,
      'country_id' => 1039, // FIXME 1039=Canada
    ];

    if (empty($office['postal'])) {
      return;
    }

    if (!isset($map[$office['type']])) {
      throw new Exception('createFromRepresentAddress: Unknown location type: ' . $values['type']);
    }

    if (empty($map[$office['type']])) {
      Civi::log()->debug("createFromRepresentAddress: type = {$values['type']}, no civicrm setting found, ignoring.");
      return;
    }

    $params['location_type_id'] = $map[$office['type']];

    // Search for an existing address
    $result = civicrm_api3('Address', 'get', [
      'contact_id' => $contact_id,
      'location_type_id' => $params['location_type_id'],
      'sequential' => 1,
    ]);

    if ($result['count']) {
      $params['id'] = $result['values'][0]['id'];
    }

    $parts = explode("\n", $office['postal']);

    // FIXME: assuming the line 1 is the main address, line 2 is appt/unit, line 3 has comment, line 4 has city, province, postcode.
    // This seem normalized in the Federal data, but is it in other data sets?
    //
    // Example:
    // 886 Thornhill Street
    // (Main Office)
    // Unit E
    // Morden MB  R6M 2E1

    // Some provinces have everything on the same line
    // ex: Parliament Buildings, Victoria BC  V8V 1X4
    // ex: Constituency:, PO Box 269, #1-16 High Street, Ladysmith, BC V9G 1A2, douglas.routley.MLA@leg.bc.ca
    if (count($parts) == 1) {
      $parts = explode(', ', $office['postal']);

      // Specific to BC in 2018
      if ($parts[0] == 'Constituency:') {
        $t = array_shift($parts);
      }
    }

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

      // BC-specific, watch to see if the last line has an email address
      if (preg_match('/@[\.a-zA-Z]+$/', $parts[1])) {
        if (empty($values['email'])) {
          $values['email'] = $parts[1];
        }

        unset($parts[1]);
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
    if (empty(self::$province_abbreviations)) {
      self::loadProvinceAbbreviations();
    }

    // We might have the city on its own line, or on the same line:
    // ex: Victoria BC  V8V 1X4
    // or just: BC  V8V 1X4
    if (preg_match('/ ([A-Z]{2})$/', $last_line, $matches) || preg_match('/^([A-Z]{2})$/', $last_line, $matches)) {
      $params['state_province_id'] = array_search($matches[1], self::$province_abbreviations);
      $last_line = preg_replace('/ [A-Z]{2}$/', '', $last_line);
      $last_line = trim($last_line);
    }

    if (empty($params['state_province_id'])) {
      Civi::log()->warning('Unknown state/province: ' . $last_line . ' -- ' . print_r($office, 1) . ' -- ' . print_r($params, 1));
      return;
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
  static public function createFromRepresentPhone($values, $office, $contact_id) {
    $map = [
      'legislature' => Civi::settings()->get('regionlookuprepresent_legislature_loctype_id'),
      'constituency' => Civi::settings()->get('regionlookuprepresent_constituent_loctype_id'),
    ];

    if (!empty($office['tel'])) {
      $params = [];

      if (!isset($map[$office['type']])) {
        Civi::log()->debug("createFromRepresentPhone: type = {$office['type']} is unknown, ignoring.");
      }

      if (empty($map[$office['type']])) {
        Civi::log()->debug("createFromRepresentPhone: type = {$office['type']}, no civicrm setting found, ignoring.");
        return;
      }

      $params['location_type_id'] = $map[$office['type']];

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
      $params['phone'] = self::cleanupPhone($office['tel']);
      $params['phone_type_id'] = 1; // FIXME

      civicrm_api3('Phone', 'create', $params);
    }

    if (!empty($office['fax'])) {
      $params = [];

      if (isset($map[$office['type']])) {
        $params['location_type_id'] = $map[$office['type']];
      }

      if (empty($params['location_type_id'])) {
        CRM_Core_Error::fatal('Unknown location type: ' . $office['type']);
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
      $params['phone'] = self::cleanupPhone($office['fax']);
      $params['phone_type_id'] = 3; // FIXME 3=fax

      civicrm_api3('Phone', 'create', $params);
    }

  }

  /**
   * Ex: 1 555 222-1212 becomes 555-222-1212
   */
  static public function cleanupPhone($phone) {
    $phone = preg_replace('/^1 /', '', $phone);
    $phone = str_replace(' ', '-', $phone);

    return $phone;
  }

  /**
   * Convert the image URL into a filename that can be saved to disk.
   *
   * Example: 'represent_[contact_id]_[randomhash].[filext]'.
   */
  static public function getConvertedImageFilename($photo_url, $contact_id) {
    if (preg_match('/\.(\w+)$/', $photo_url, $matches)) {
      $photo_url = 'represent_' . $contact_id . '_' . sha1($photo_url) . '.' . $matches[1];
      return $photo_url;
    }

    // Otherwise assume JPG
    // Ex: Quebec returns oddly named '.aspx' images.
    $photo_url = 'represent_' . $contact_id . '_' . sha1($photo_url) . '.jpg';
    return $photo_url;
  }

  /**
   * Given a photo_url and a contact_id, fetch the image and save it
   * locally, set the image as the contact image_URL.
   */
  static public function saveImageToDisk($photo_url, $contact_id) {
    if (empty($photo_url)) {
      return NULL;
    }

    $image_filename = self::getConvertedImageFilename($photo_url, $contact_id);

    $config = CRM_Core_Config::singleton();
    $disk_file_name = $config->customFileUploadDir . '/' . $image_filename;

    // Fetch with Guzzle instead of Curl, because some filenames have accents
    // and Guzzle handles all the things you would expect it to.
    try {
      $client = new GuzzleHttp\Client();

      $response = $client->request('GET', $photo_url, [
        'headers' => [
          'User-Agent' => 'CiviCRM RegionLookupRepresent',
        ],
        // Save to disk
        // http://guzzle.readthedocs.io/en/latest/request-options.html#sink-option
        'sink' => $disk_file_name,
      ]);

      // In civicrm_contact.image_URL, we need to store an URL such as:
      // https://example.org/civicrm/contact/imagefile?photo=represent_152_1be83a292ad8f4c8f82728624faf453a7485fe11.jpg
      $image_url = CRM_Utils_System::url('civicrm/contact/imagefile', "photo={$image_filename}");

      civicrm_api3('Contact', 'create', [
        'id' => $contact_id,
        'image_URL' => $image_url,
      ]);
    }
    catch (Exception $e) {
      Civi::log()->warning('RegionlookupRepresent: failed to download image.', [
        'url' => $photo_url,
        'error' => $e->getMessage(),
      ]);
    }
  }

  static public function loadProvinceAbbreviations() {
    $canada_id = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_country WHERE name = 'Canada'");
    $whereClause = 'country_id = ' . $canada_id;

    CRM_Core_PseudoConstant::populate(self::$province_abbreviations, 'CRM_Core_DAO_StateProvince', TRUE, 'abbreviation', 'is_active', $whereClause);
  }

  /**
   *
   */
  static public function findRiding($name, $contact_sub_type) {
    $result = civicrm_api3('Contact', 'get', [
      'contact_type' => 'Organization',
      'contact_sub_type' => $contact_sub_type,
      'organization_name' => $name,
      'return.contact_id' => 1,
      'sequential' => 1,
    ]);

    if ($result['count']) {
      return $result['values'][0]['contact_id'];
    }

    // Search for variants because sometimes names have '—' and sometimes '-'.
    if (strpos($name, '—') !== FALSE) {
      $name = str_replace('—', '-', $name);

      $result = civicrm_api3('Contact', 'get', [
        'contact_type' => 'Organization',
        'contact_sub_type' => $contact_sub_type,
        'organization_name' => $name,
        'return.contact_id' => 1,
        'sequential' => 1,
      ]);

      if ($result['count']) {
        return $result['values'][0]['contact_id'];
      }
    }
    elseif (strpos($name, '-') !== FALSE) {
      $name = str_replace('-', '—', $name);

      $result = civicrm_api3('Contact', 'get', [
        'contact_type' => 'Organization',
        'contact_sub_type' => $contact_sub_type,
        'organization_name' => $name,
        'return.contact_id' => 1,
        'sequential' => 1,
      ]);

      if ($result['count']) {
        return $result['values'][0]['contact_id'];
      }
    }

    return NULL;
  }

}
