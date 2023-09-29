<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


use numero2\FormCodesBundle\FormCodeModel;


$GLOBALS['TL_DCA']['tl_form']['config']['ctable'][] = FormCodeModel::getTable();

$GLOBALS['TL_DCA']['tl_form']['list']['operations']['codes'] = [
    'href'      => 'table=' . FormCodeModel::getTable()
,   'icon'      => 'bundles/formcodes/backend/img/icon_codes.svg'
];

$GLOBALS['TL_DCA']['tl_form']['fields']['has_form_codes'] = [
    'inputType' => 'checkbox'
,   'filter'    => true
,   'eval'      => ['doNotCopy'=>true, 'submitOnChange'=>true]
,   'sql'       => "char(1) NOT NULL default ''"
];