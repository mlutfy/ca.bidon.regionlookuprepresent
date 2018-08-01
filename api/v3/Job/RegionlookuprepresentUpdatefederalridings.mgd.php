<?php

/**
 * @file
 * This file declares a managed database record of type "Job".
 */

return [
  0 => [
    'name' => 'Cron:Job.RegionlookuprepresentUpdatefederalridings',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Region Lookup Represent Update Federal Ridings',
      'description' => 'Updates Federal MP information using Open North Represent',
      'run_frequency' => 'Weekly',
      'api_entity' => 'Job',
      'api_action' => 'regionlookuprepresentupdatefederalridings',
      'parameters' => '',
    ],
    'update' => 'never',
  ],
];
