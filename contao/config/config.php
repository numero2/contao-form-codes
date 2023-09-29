<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


use Contao\FormModel;
use Contao\System;
use numero2\FormCodesBundle\FormCodeModel;
use numero2\FormCodesBundle\ModuleFormCodesImport;


$requestStack = System::getContainer()->get('request_stack');
$scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');


/**
 * MODELS
 */
$GLOBALS['TL_MODELS'][FormCodeModel::getTable()] = FormCodeModel::class;


/**
 * BACK END MODULES
 */
$GLOBALS['BE_MOD']['content']['form']['tables'][] = FormCodeModel::getTable();
$GLOBALS['BE_MOD']['content']['form']['importFormCodes'] = [ModuleFormCodesImport::class, 'renderForm'];


/**
 * BACK END JAVASCRIPT / STYLESHEET
 */
if( $requestStack->getCurrentRequest() && $scopeMatcher->isBackendRequest($requestStack->getCurrentRequest()) ) {
    $GLOBALS['TL_CSS'][] = 'bundles/formcodes/backend/css/backend.css';
}


/**
 * NOTIFICATION CENTER NOTIFICATION TYPES
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_subject'][] = 'fc_code';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_text'][] = 'fc_code';
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form']['email_html'][] = 'fc_code';