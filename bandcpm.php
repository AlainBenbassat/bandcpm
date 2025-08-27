<?php

require_once 'bandcpm.civix.php';

use CRM_Bandcpm_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function bandcpm_civicrm_config(&$config): void {
  _bandcpm_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function bandcpm_civicrm_install(): void {
  _bandcpm_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function bandcpm_civicrm_enable(): void {
  _bandcpm_civix_civicrm_enable();
}
