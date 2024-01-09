<?php

require_once 'regionlookuprepresent.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function regionlookuprepresent_civicrm_config(&$config) {
  _regionlookuprepresent_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function regionlookuprepresent_civicrm_install() {
  _regionlookuprepresent_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function regionlookuprepresent_civicrm_enable() {
  _regionlookuprepresent_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_regionlookup_config()
 * from ca.bidon.regionlookup.
 */
function regionlookuprepresent_civicrm_regionlookup_config(&$methods) {
  $methods['CRM_Regionlookuprepresent_BAO_RegionLookup'] = ts('Represent (Open North)');
}

/**
 * Implements hook_civicrm_pageRun().
 */
function regionlookuprepresent_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');

  if ($pageName == 'CRM_Contact_Page_View_Summary') {
    CRM_Regionlookuprepresent_Contact_Page_View_Summary::pageRun($page);
  }
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 */
function regionlookuprepresent_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  // This is all public information
  // Although, it might be better to have a custom permission.
  $permissions['regionlookuprepresent']['getlegislator'] = array('access AJAX API');
  $permissions['regionlookuprepresent']['lookupridingbygeo'] = array('access AJAX API');
  $permissions['regionlookuprepresent']['lookuprepbydistrict'] = array('access AJAX API');
  $permissions['regionlookuprepresent']['lookupridingbypostcode'] = array('access AJAX API');
  $permissions['regionlookuprepresent']['lookupridingbyrepname'] = array('access AJAX API');
  $permissions['regionlookuprepresent']['lookupboundary'] = array('access AJAX API');
}
