<?php
use CRM_MembershipPeriodTracker_ExtensionUtil as E;

class CRM_MembershipPeriodTracker_Page_MembershipPeriodHistory extends CRM_Core_Page {

    /**
    * @var CRM_Utils_Pager
    */
    protected $_pager = NULL;

    /**
    * Contacts ID if its showing under tab
    * @var null or integer
    * @since 1.0
    */
    public $_contactId = NULL;

    public function run() {

        CRM_Utils_System::setTitle(E::ts('Membership Period History'));

        try {

          $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);

        } catch (CRM_Core_Exception $e) { // if there is any problem/error then throw away user from the Error.

          CRM_Utils_System::setHttpHeader( 'Location', civicrm_home_url() );
          exit();
        }

        $this->ajaxResponse['title'] = "MembershipPeriodHistory";

        $this->ajaxResponse['userContext'] = CRM_Utils_System::url( 'civicrm/contact/view/membershipperiodhistory',
            "force=1" );

        // Setting Tab count on this page loaded over ajax
        $this->ajaxResponse['tabCount'] = civicrm_api3( 'MembershipPeriod', 'getcount', array( 'contact_id' => $this->_contactId ) );

        // I don't know why this is needed!, i can't find any logic behind this so i think i'm doing(assigning form.formClass) this because there is an error/bug with pager CRM/common/pager.tpl
        // Take a look at CRM/common/pager.tpl on 63 Number line
        // And take a look at templates/CRM/MembershipPeriodTracker/Page/MembershipPeriodHistory.tpl on 1 Number line
        $this->assign( 'form', array( 'formClass' => CRM_Utils_System::getClassName($this) ) );

        // Show data with Pagination supported
        $this->browse();

        parent::run();
    }

    /**
     * Browse all Membership Period History.
     *
     * @param mixed $action
     *   Unused parameter.
     */
    public function browse() {

        $this->pager();

        list($offset, $rowCount) = $this->_pager->getOffsetAndRowCount();

        // All the data we are going to show
        // Getting all Membership Period by Contact ID. if $_contactId is null then it return value for */all
        $periodHistoryParamsOptions = array( 'limit' => $rowCount, 'offset' => $offset, 'sort' => 'id desc' );
        try {
            $sort = CRM_Utils_Request::retrieve('sort', 'String', $this);

            // if sort was found in url then go ahead to modify options
            // little bit validation, i know this is not enough but it just for validation hint!
            if ( !empty( $sort ) && substr_count( $sort, ' ' ) <= 1 ) {
                $periodHistoryParamsOptions['sort'] = $sort;
            }

            $this->generateAssignSortingLinksClasses($sort);

        } catch (CRM_Core_Exception $e) {
            // Do nothing, just ignore it
        }

        $periodHistoryParams = array(
            'sequential' => 1,
            'contact_id' => $this->_contactId,
            'options' => $periodHistoryParamsOptions, // Not using end_date because end_date can be null when Membership type is Lifetime
        );


        // Getting Membership Period by Contact ID. Amount/limit is set by Pager, using $rowCount, $offset.  if $_contactId is null then it return latest for all contact.
        $periodHistoryResult = civicrm_api3('MembershipPeriod', 'get', $periodHistoryParams)['values'];

        $periodHistory = array();

        // Loop through each period/item and collect necessary data to an Array($periodHistory) which are gonna assign to our template.
        foreach ( $periodHistoryResult as $period ){

            $periodTmp = array();

            try {
                $membershipTypeName = civicrm_api3('MembershipType', 'getvalue', array(
                    'return' => "name",
                    'id' => $period['membership_type_id'],
                ));
            } catch (CiviCRM_API3_Exception $e) {
                echo "Something wrong happens! please try again later";
                break;
            }

            $periodTmp['id']              = $period['id'];
            $periodTmp['contact_id']      = $period['contact_id'];
            $periodTmp['start_date']      = $period['start_date'];
            $periodTmp['end_date']        = isset( $period['end_date'] ) ? $period['end_date'] : null;
            $periodTmp['membership_type'] = $membershipTypeName;
            $periodTmp['contribution_id'] = isset( $period['contribution_id'] ) ? $period['contribution_id'] : null;
            $periodTmp['membership_id']   = $period['membership_id'];

            $periodHistory[] = $periodTmp;

        }
        // Assigning data into template so we can show it to the user
        $this->assign( 'rows', $periodHistory );

    }


    /**
     * Setting up $_pager for pagination support
     * Used CRM_Utils_Pager & CRM/common/pager.tpl to handle pagination
     */
    public function pager() {
        // Setting up parameters for Pager that we are gonna create
        $params['status'] = ts('Membership Period %%StatusMessage%%');
        $params['csvString'] = NULL;
        $params['buttonTop'] = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
        $params['rowCount'] = $this->get(CRM_Utils_Pager::PAGE_ROWCOUNT);
        if (!$params['rowCount']) {
            $params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
        }

        // Getting all Membership Period by Contact ID. if $_contactId is null then it return value for all Contact.
        $params['total'] = civicrm_api3( 'MembershipPeriod', 'getcount', array( 'contact_id' => $this->_contactId ) );;

        // Setup Pager object and assign into template so it can be used by CRM/common/pager.tpl
        $this->_pager = new CRM_Utils_Pager($params);
        $this->assign_by_ref('pager', $this->_pager);
    }

    /**
    * Get BAO Name.
    *
    * @return string
    *   Classname of BAO.
    */
    public function getBAOName() {
      return 'CRM_MembershipPeriodTracker_BAO_MembershipPeriod';
    }

    /**
     * @param $sort
     *
     * generating links & classes for sorting thead>tr>a in MembershipPeriodHistory.tpl
     *
     * @since 1.0
     */
    private function generateAssignSortingLinksClasses($sort) {

        // Making links for sorting
        // it is used in MembershipPeriodHistory.tpl on 16 Number line
        // Not doing automatic system or anything like that to do this sort detection!
        /*************** sort by start_date *****************/
        if ($sort == 'start_date asc') {
            $this->assign('sort_by_start_date_class', 'sorting_asc');
            $this->assign('sort_by_start_date_link', $this->_pager->makeURL('sort', 'start_date desc'));
        } elseif ($sort == 'start_date desc') {
            $this->assign('sort_by_start_date_class', 'sorting_desc');
            $this->assign('sort_by_start_date_link', $this->_pager->makeURL('sort', 'start_date asc'));
        } else {
            $this->assign('sort_by_start_date_class', 'sorting');
            $this->assign('sort_by_start_date_link', $this->_pager->makeURL('sort', 'start_date asc'));
        }

        /*************** sort by end_date *****************/
        if ($sort == 'end_date asc') {
            $this->assign('sort_by_end_date_class', 'sorting_asc');
            $this->assign('sort_by_end_date_link', $this->_pager->makeURL('sort', 'end_date desc'));
        } elseif ($sort == 'end_date desc') {
            $this->assign('sort_by_end_date_class', 'sorting_desc');
            $this->assign('sort_by_end_date_link', $this->_pager->makeURL('sort', 'end_date asc'));
        } else {
            $this->assign('sort_by_end_date_class', 'sorting');
            $this->assign('sort_by_end_date_link', $this->_pager->makeURL('sort', 'end_date asc'));
        }

        /*************** sort by membership_type *****************/
        if ($sort == 'membership_type_id asc') {
            $this->assign('sort_by_membership_type_class', 'sorting_asc');
            $this->assign('sort_by_membership_type_link', $this->_pager->makeURL('sort', 'membership_type_id desc'));
        } elseif ($sort == 'membership_type_id desc') {
            $this->assign('sort_by_membership_type_class', 'sorting_desc');
            $this->assign('sort_by_membership_type_link', $this->_pager->makeURL('sort', 'membership_type_id asc'));
        } else {
            $this->assign('sort_by_membership_type_class', 'sorting');
            $this->assign('sort_by_membership_type_link', $this->_pager->makeURL('sort', 'membership_type_id asc'));
        }

        /*************** sort by contribution *****************/
        if ($sort == 'contribution_id asc') {
            $this->assign('sort_by_contribution_class', 'sorting_asc');
            $this->assign('sort_by_contribution_link', $this->_pager->makeURL('sort', 'contribution_id desc'));
        } elseif ($sort == 'contribution_id desc') {
            $this->assign('sort_by_contribution_class', 'sorting_desc');
            $this->assign('sort_by_contribution_link', $this->_pager->makeURL('sort', 'contribution_id asc'));
        } else {
            $this->assign('sort_by_contribution_class', 'sorting');
            $this->assign('sort_by_contribution_link', $this->_pager->makeURL('sort', 'contribution_id asc'));
        }

    }

}
