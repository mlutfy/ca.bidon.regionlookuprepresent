<?php

/**
 * @file
 * This file declares a managed database record of type "Job".
 */

return [
  0 => [
    'name' => 'Cron:Job.RegionlookuprepresentUpdateprovincialridings',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Region Lookup Represent Update Provincial Ridings',
      'description' => 'Updates Provincial MLA/MNA information using Open North Represent',
      'run_frequency' => 'Weekly',
      'api_entity' => 'Job',
      'api_action' => 'regionlookuprepresentupdateprovincialridings',
      'parameters' => '',
    ],
    'update' => 'never',
  ],
];
