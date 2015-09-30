<?php

class CRM_Regionlookuprepresent_BAO_RegionLookup {
  /**
   *
   * Returns an array of results.
   */
  static function lookup($value) {
    $results = array();
    $results[0] = array();

    $fields = CRM_RegionLookup_BAO_RegionLookup::getFields();
    $settings = CRM_Core_BAO_Setting::getItem(REGIONLOOKUP_SETTINGS_GROUP);

    $value = strtoupper($value);
    $value = preg_replace('/[^A-Z0-9]/', '', $value);

    $result = file_get_contents('https://represent.opennorth.ca/postcodes/' . $value . '/');

    if (! empty($result)) {
      $data = json_decode($result);

      if (! empty($data)) {
        foreach ($data->boundaries_centroid as $region) {
          if ($region->boundary_set_name == "Montréal borough") {
            $results[0]['borough'] = $region->external_id;
          }
          elseif ($region->boundary_set_name == "Montréal district") {
            // Represent returns, ex: 17.1, but this is rather impractical, 171 can be stored as int.
            $results[0]['district'] = str_replace('.', '', $region->external_id);
          }
          elseif ($region->boundary_set_name == "Federal electoral district") {
            $results[0]['country_riding'] = $region->external_id;
          }
        }
      }
    }

    return $results;
  }
}
