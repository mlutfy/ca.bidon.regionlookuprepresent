<?php

class CRM_Regionlookuprepresent_Contact_Page_View_Summary {

  static public function pageRun(&$page) {
    $contact_id = $page->getVar('_contactId');

    // Show riding elected official summary
    $summary = self::getRidingSummary($contact_id);
    $page->assign('ridingSummary', $summary);

    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/Regionlookuprepresent/Contact/Page/View/RidingSummary.tpl',
    ));
  }

  /**
   *
   */
  static public function getRidingSummary($contact_id) {
    $result = civicrm_api3('Contact', 'getsingle', [
      'id' => $contact_id,
      'return.nick_name' => 1,
      'return.custom_3' => 1, // FIXME hardcoded value, fetch from a setting
    ]);

    return [
      'elected_official_name' => $result['nick_name'],
      'elected_official_party' => $result['custom_3'],
    ];
  }

}
