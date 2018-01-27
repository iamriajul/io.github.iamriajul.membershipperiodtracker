<?php
use CRM_MembershipPeriodTracker_ExtensionUtil as E;

class CRM_MembershipPeriodTracker_BAO_MembershipPeriod extends CRM_MembershipPeriodTracker_DAO_MembershipPeriod {

  /**
   * Create a new MembershipPeriod based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_MembershipPeriodTracker_DAO_MembershipPeriod|NULL
   *
   */

  public static function create($params) {
    $className = 'CRM_MembershipPeriodTracker_DAO_MembershipPeriod';
    $entityName = 'MembershipPeriod';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new CRM_MembershipPeriodTracker_DAO_MembershipPeriod();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
    return $instance;
  }

}
