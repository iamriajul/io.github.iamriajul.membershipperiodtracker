<?php

require_once 'membershipperiodtracker.civix.php';
use CRM_Membershipperiodtracker_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function membershipperiodtracker_civicrm_config(&$config) {
  _membershipperiodtracker_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function membershipperiodtracker_civicrm_xmlMenu(&$files) {
  _membershipperiodtracker_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function membershipperiodtracker_civicrm_install() {
  _membershipperiodtracker_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function membershipperiodtracker_civicrm_postInstall() {
  _membershipperiodtracker_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function membershipperiodtracker_civicrm_uninstall() {
  _membershipperiodtracker_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function membershipperiodtracker_civicrm_enable() {
  _membershipperiodtracker_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function membershipperiodtracker_civicrm_disable() {
  _membershipperiodtracker_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function membershipperiodtracker_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _membershipperiodtracker_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function membershipperiodtracker_civicrm_managed(&$entities) {
  _membershipperiodtracker_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function membershipperiodtracker_civicrm_caseTypes(&$caseTypes) {
  _membershipperiodtracker_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function membershipperiodtracker_civicrm_angularModules(&$angularModules) {
  _membershipperiodtracker_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function membershipperiodtracker_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _membershipperiodtracker_civix_civicrm_alterSettingsFolders($metaDataFolders);
}




/**
 * Implementation hook_civicrm_post()
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 *
 * in this hook, we are tracking/recording Membership creation/renewal Periods and/or Contribution.
 *
 * @since 1.0
 * @throws CiviCRM_API3_Exception
 */

function membershipperiodtracker_civicrm_post($op, $objectName, $objectId, &$objectRef) {

    // IDE Help
    /** @var array( [id], [start_date], [end_date], [contact_id], [membership_id], [membership_type_id], [contribution_id] ) $params */


    /********************************* Saving Membership create/renewal/extending date History *******************************/

    // When user created/renewed Membership(Entity) but not yet saved Contribution
    if ( $objectName == 'Membership' ) {
        /** @var CRM_Member_DAO_Membership $objectRef */

        // Parameters for Membership Period.
        $params = array();
        $params['end_date']             = $objectRef->end_date;
        $params['contact_id']           = $objectRef->contact_id;
        $params['membership_id']        = $objectId;
        $params['membership_type_id']   = $objectRef->membership_type_id;
        $params['contribution_id']      = null; // Contribution not yet saved so can't get the Contribution Id here. look at: *** Update Contribution ID ***


        if ( $op == 'create' ) {

            $params['start_date']           = $objectRef->start_date;

            CRM_MembershipPeriodTracker_BAO_MembershipPeriod::create( $params );

        } elseif ( $op == 'edit' ) {

            try {

                // We are getting Membership Log so we can crack out start_date if the Membership was edited for renewal/or extending date.
                $membershipLogResult = civicrm_api3('MembershipLog', 'get', array(
                  'sequential'     => 1,
                  'membership_id'  => $objectId,
                  'end_date'       => $objectRef->end_date, // Make sure right Log get selected by means we will only get one item/Log
                ) );

                $membershipLogCount     = $membershipLogResult['count'];

            } catch (CiviCRM_API3_Exception $e) {
                return;
            }


            // This Log will give me the Last changes was made, so i can get the start date of the Membership($objectRef->membership_id) renewal
            $lastMembershipLog = $membershipLogResult['values'][$membershipLogCount - 1];


            $params['start_date']           = $lastMembershipLog['start_date'];

            // Prevent duplicate date populating // Note: We are not going to prevent extending date by using Editing features (if its needed u can ask me)
            // Here by meaning duplicate is: When user edited Membership field except/but Membership field that are associated(Foreign Key) with our Table(civicrm_membership_period).
            try {

                $duplicatePeriodCount = civicrm_api3( 'MembershipPeriod', 'getcount', $params );

            } catch (CiviCRM_API3_Exception $e) {
                $duplicatePeriodCount = 1; // Don't wanna take risk!
            }

            if ( $duplicatePeriodCount == 0 ) { // Populate only when there is no same Period with same(as now) data.
                CRM_MembershipPeriodTracker_BAO_MembershipPeriod::create( $params );
            }

        }


    }


    /************************ Update Contribution ID  ***************************/

    // When user create or renew Membership(Entity) with checked `Record Membership Payment?`
    // It will run when Membership created/renewed and saved all data
    if ( $objectName == 'MembershipPayment' && $op == 'create' ) {
        /** @var CRM_Member_DAO_MembershipPayment $objectRef */

        // there is no way to be this Membership(from Membership Payment Obj ref) empty or does not exist!!!
        $membershipParams = array(
            'id'        => $objectRef->membership_id,
        );

        try {
            // Payment was made for this Membership($objectRef->membership_id)
            $membership = civicrm_api3( 'Membership', 'get', $membershipParams )['values'][$objectRef->membership_id];

            // Get Membership Log for cracking start_date and end_date
            $membershipLogResult    = civicrm_api3( 'MembershipLog', 'get', array(
                'sequential'          => 1,
                'membership_id'       => $objectRef->membership_id,
                'membership_type_id'  => $membership['membership_type_id'],
                'options'             => array( 'sort' => 'start_date desc', 'limit' => 1 ),  // Not using end_date because end_date can be null when Membership type is Lifetime
            ) );

        } catch (CiviCRM_API3_Exception $e) {
          return;
        }

        // This Log will give me the Last changes was made in the Membership, so i can get the start and end date of the Membership($objectRef->membership_id) renewal
        $lastMembershipLog = $membershipLogResult['values'][0]; // Result is sequential array

        try {

          // We are gonna update our last added Period with added Contribution ID
          // So thats why i am getting the last added Period ID using nearly all data except Contribution Id by doing this it will return only one result/id, look at getvalue Parameters to understand
          $lastAddedPeriodId = civicrm_api3( 'MembershipPeriod', 'getvalue', array(
              'return'              => "id",
              'start_date'          => $lastMembershipLog['start_date'],
              'end_date'            => $lastMembershipLog['end_date'],
              'contact_id'          => $membership['contact_id'],
              'membership_id'       => $objectRef->membership_id,
              'membership_type_id'  => $membership['membership_type_id'],
          ) );

        } catch (CiviCRM_API3_Exception $e) {
          return; // Get out of here!
        }


        // Parameters for Membership Period.
        $params = array();

        // Collection Membership Period data
        $params['id']                   = $lastAddedPeriodId;
        $params['start_date']           = $lastMembershipLog['start_date'];
        $params['end_date']             = $lastMembershipLog['end_date'];
        $params['contact_id']           = $membership['contact_id'];
        $params['membership_id']        = $objectRef->membership_id;
        $params['membership_type_id']   = $membership['membership_type_id'];
        $params['contribution_id']      = $objectRef->contribution_id;

        // It will update WHERE $params['id'] eq $lastAddedPeriodId
        CRM_MembershipPeriodTracker_BAO_MembershipPeriod::create( $params );

    }

}


// TODO add backward compatibility for tabset using tabs

/**
 * Implementation hook_civicrm_tabset()
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabset
 *
 * in this hook, added our own Tab called Membership Period History in Contact
 *
 * @since 1.0
 * @throws CiviCRM_API3_Exception
 */
function membershipperiodtracker_civicrm_tabset($tabsetName, &$tabs, $context) {

  // When Contact View TabSet we are going to add our own Tab called Membership Period History
  if ( $tabsetName == 'civicrm/contact/view' && isset( $context['contact_id'] ) ){

      $contactId =  $context['contact_id'];

      $url = CRM_Utils_System::url( 'civicrm/contact/view/membershipperiodhistory',
          "reset=1&force=1&cid=$contactId" );

      $periodHistoryCount = civicrm_api3('MembershipPeriod', 'getcount', array( 'contact_id' => $contactId ) );

      $tab = array(
          'id'      => 'membership_period_history',
          'title'   => 'Membership Period History',
          'url'     => $url,
          'valid'   => true,
          'active'  => true,
          'class'   => 'livePage',
          'current' => false,
          'weight'  => 31, // After Membership
          'count'   => $periodHistoryCount
      );


      $tabs[] = $tab;

  }

}

function membershipperiodtracker_civicrm_pageRun(&$page) {
    /** @var CRM_Core_Page $page */
    $pageName = $page->getVar('_name');

    // Add our Membership Period History to Membership Tab Page(CRM/Member/Page/Tab.php) Ajax Response Update tabs ajaxResponse[updateTabs]
    // So our tab get updated automatically when Memberships Tab
    if ($pageName == 'CRM_Member_Page_Tab') {
        /** @var CRM_Member_Page_Tab $page */

        $page->ajaxResponse['updateTabs']['#tab_membership_period_history'] = civicrm_api3( 'MembershipPeriod', 'getcount', array( 'contact_id' => $page->_contactId ) );
    }

}

/**
 * Setup All mine Entity
 * Implements hook_civicrm_entityTypes().
 */
function membershipperiodtracker_civicrm_entityTypes(&$entityTypes) {

  $entityTypes[] = array (
      'name'  => 'MembershipPeriod',
      'class' => 'CRM_MembershipPeriodTracker_DAO_MembershipPeriod',
      'table' => 'civicrm_membership_period',
  );

}
