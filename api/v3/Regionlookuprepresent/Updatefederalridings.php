<?php

function _civicrm_api3_regionlookuprepresent_updatefederalridings_spec(&$spec) {
  $spec['contact_sub_type']['api.required'] = 1;
}

function civicrm_api3_regionlookuprepresent_updatefederalridings($params) {
  $options = array('http' => array('user_agent' => 'CiviCRM RegionLookupRepresent'));
  $context  = stream_context_create($options);
  $result = file_get_contents('https://represent.opennorth.ca/representatives/house-of-commons/?limit=999', FALSE, $context);

  $contact_type = $params['contact_sub_type'];

  if (!empty($result)) {
    $data = json_decode($result, TRUE);

    foreach ($data['objects'] as $key => $values) {
      CRM_Regionlookuprepresent_BAO_Riding::createFromRepresent($values, $params['contact_sub_type']);
    }
  }
}
