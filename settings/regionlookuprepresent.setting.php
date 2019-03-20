<?php

use CRM_Regionlookuprepresent_ExtensionUtil as E;

/**
 * Settings metadata file
 */
return [
  'regionlookuprepresent_federalriding_ctype' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_federalriding_ctype',
    'subsection' => 'federalriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Federal riding contact type'),
    'description' => E::ts('Assumes that this is a subtype of Organization. If empty, federal ridings will not be synched from Represent.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'ContactType',
      'api_field' => 'label',
    ),
  ],
  'regionlookuprepresent_federalriding_cfid' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_federalriding_cfid',
    'subsection' => 'federalriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Federal riding custom field'),
    'description' => E::ts('Custom field where to store the federal riding (ex: on organisation or individual records, not the riding itself).'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'CustomField',
      'api_field' => 'label',
    ),
  ],
  'regionlookuprepresent_federalriding_nickname' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_federalriding_nickname',
    'subsection' => 'federalriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Add the MP name in the federal riding nickname field?'), // FIXME: we also use this to add the photo to the riding's record
    'description' => E::ts("This can help with looking up ridings using the MP's name."),
    'help_text' => '',
    'quick_form_type' => 'YesNo',
    'html_type' => 'Radio',
  ],
  'regionlookuprepresent_federalriding_mp_ctype' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_federalriding_mp_ctype',
    'subsection' => 'federalriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Federal MP individual contact type'),
    'description' => E::ts('Assumes that this is a subtype of Individual. If empty, federal MPs will not be synched from Represent.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'ContactType',
      'api_field' => 'label',
    ),
  ],
  'regionlookuprepresent_federalriding_mp_reltype' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_federalriding_mp_reltype',
    'subsection' => 'federalriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Federal MP relationship type'),
    'description' => E::ts('Relationship type to the MP individual contact record. If empty, individual MPs will ont be created.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'RelationshipType',
      'api_field' => 'label_a_b',
    ),
  ],
  'regionlookuprepresent_provincialriding_ctype' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_provincialriding_ctype',
    'subsection' => 'provincialriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Provincial riding contact type'),
    'description' => E::ts('Assumes that this is a subtype of Organization. If empty, provincial ridings will not be synched from Represent.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'ContactType',
      'api_field' => 'label',
    ),
  ],
  'regionlookuprepresent_provincialriding_cfid' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_provincialriding_cfid',
    'subsection' => 'provincialriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Provincial riding custom field'),
    'description' => E::ts('Custom field where to store the provincial riding (ex: on organisation or individual records, not the riding itself).'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'CustomField',
      'api_field' => 'label',
    ),
  ],
  'regionlookuprepresent_provincialriding_mp_reltype' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_provincialriding_mp_reltype',
    'subsection' => 'provincialriding',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Provincial MLA/MNA relationship type'),
    'description' => E::ts('Relationship type to the MLA/MNA individual contact record. If empty, individual MLA/MNAs will ont be created.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'RelationshipType',
      'api_field' => 'label_a_b',
    ),
  ],
  'regionlookuprepresent_legislature_loctype_id' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_legislature_loctype_id',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Legislature Location Type'),
    'description' => E::ts('This should be something specific, such as a "Riding Legislature Address" location type. It should be different than the constituent address type. If empty, legislature phones and addresses will not be imported. This applies to both federal and provincial legislators.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'LocationType',
      'api_field' => 'display_name',
    ),
  ],
  'regionlookuprepresent_constituent_loctype_id' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_constituent_loctype_id',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Constituent Location Type'),
    'description' => E::ts('This should be something specific, such as a "Constituent Legislature Address" location type. It should be different than the legislature address type. If empty, constituent phones and addresses will not be imported. This applies to both federal and provincial legislators.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'LocationType',
      'api_field' => 'display_name',
    ),
  ],
  'regionlookuprepresent_email_loctype_id' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_email_loctype_id',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Email Location Type'),
    'description' => E::ts('Legislators usually have only one official e-mail (legislator address). If empty, email addresses will not be imported. This applies to both federal and provincial legislators.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'LocationType',
      'api_field' => 'display_name',
    ),
  ],
  'regionlookuprepresent_website_type_id' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_website_type_id',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Website Type for Main Website'),
    'description' => E::ts('How to categorise their main website. If empty, email addresses will not be imported. This applies to both federal and provincial legislators.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'Website',
      'api_getoptions' => 'website_type_id',
    ),
  ],
  'regionlookuprepresent_personalwebsite_type_id' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_personalwebsite_type_id',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Website Type for Personal Site'),
    'description' => E::ts('How to categorise their Personal Site URL. If empty, email addresses will not be imported. This applies to both federal and provincial legislators.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'Website',
      'api_getoptions' => 'website_type_id',
    ),
  ],
  'regionlookuprepresent_twitter_type_id' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_twitter_type_id',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Website Type for Twitter'),
    'description' => E::ts('How to categorise their Twitter URL. If empty, email addresses will not be imported. This applies to both federal and provincial legislators.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'Website',
      'api_getoptions' => 'website_type_id',
    ),
  ],
  'regionlookuprepresent_party_field' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_party_field',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Political Party Field'),
    'description' => E::ts('Custom field to identify the political party. Currently, only a text field is supported. Used for both federal and provincial.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'CustomField',
      'api_field' => 'label',
    ),
  ],
  'regionlookuprepresent_boundary_url' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_boundary_url',
    'subsection' => 'ridingsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Political Boundary URL Field'),
    'description' => E::ts('Custom field to store the boundary URL (for generating maps). Used for both federal and provincial.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'CustomField',
      'api_field' => 'label',
    ),
  ],
  'regionlookuprepresent_contact_loctype_id' => [
    'group_name' => 'domain',
    'group' => 'regionlookuprepresent',
    'name' => 'regionlookuprepresent_contact_loctype_id',
    'subsection' => 'contactsetting',
    'type' => 'Integer',
    'default' => NULL,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Contact Lookup Location Type'),
    'description' => E::ts('Location Type of the contact (individual or organisation, but generally not a riding) that will be looked up to see in which riding it is. The lookup is done using the geographic coordinates, so address geociding must be enabled.'),
    'help_text' => '',
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'html_attributes' => array(
      'class' => 'crm-select2',
    ),
    'pseudoconstant' => array(
      'api_entity' => 'LocationType',
      'api_field' => 'display_name',
    ),
  ],
];
