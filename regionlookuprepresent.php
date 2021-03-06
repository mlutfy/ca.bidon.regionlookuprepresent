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
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function regionlookuprepresent_civicrm_xmlMenu(&$files) {
  _regionlookuprepresent_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function regionlookuprepresent_civicrm_uninstall() {
  _regionlookuprepresent_civix_civicrm_uninstall();
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
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function regionlookuprepresent_civicrm_disable() {
  _regionlookuprepresent_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function regionlookuprepresent_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _regionlookuprepresent_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_regionlookup_config()
 * from ca.bidon.regionlookup.
 */
function regionlookuprepresent_civicrm_regionlookup_config(&$methods) {
  $methods['CRM_Regionlookuprepresent_BAO_RegionLookup'] = ts('Represent (Open North)');
}

/**
 * Implements hook_civicrm_config().
 *
 * @param $metaDataFolders
 */
function regionlookuprepresent_civicrm_alterSettingsFolders(&$metaDataFolders){
  static $configured = FALSE;
  if ($configured) return;
  $configured = TRUE;

  $extRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  $extDir = $extRoot . 'settings';
  if(!in_array($extDir, $metaDataFolders)){
    $metaDataFolders[] = $extDir;
  }
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function regionlookuprepresent_civicrm_managed(&$entities) {
  _regionlookuprepresent_civix_civicrm_managed($entities);
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
