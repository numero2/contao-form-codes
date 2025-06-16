<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


use Contao\DC_Table;
use Contao\FormModel;
use Contao\MemberModel;
use numero2\FormCodesBundle\FormCodeModel;


$GLOBALS['TL_DCA'][FormCodeModel::getTable()] = [

    'config' => [
        'dataContainer'             => DC_Table::class
    ,   'ptable'                    => FormModel::getTable()
    ,   'switchToEdit'              => true
    ,   'enableVersioning'          => true
    ,   'sql' => [
            'keys' => [
                'id' => 'primary'
            ,   'pid' => 'index'
            ,   'pid,code' => 'unique'
            ]
        ]
    ]
,   'list' => [
        'sorting' => [
            'mode'                  => 2
        ,   'fields'                => ['tstamp']
        ,   'panelLayout'           => 'filter;search,limit'
        ]
    ,   'label' => [
            'fields'                => ['claimed','code','member']
        ,   'showColumns'            => true
        ]
    ,   'global_operations' => [
            'import' => [
                'href'                => 'key=importFormCodes'
            ,   'class'               => 'header_icon header_form_codes_import'
            ,   'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ]
        ,   'all' => [
                'href'              => 'act=select'
            ,   'class'             => 'header_edit_all'
            ,   'attributes'        => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ]
    ,   'operations' => [
            'edit' => [
                'href'              => 'act=edit'
            ,   'icon'              => 'edit.svg'
            ]
        ,   'delete' => [
                'href'              => 'act=delete'
            ,   'icon'              => 'delete.svg'
            ,   'attributes'        => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\')) return false; Backend.getScrollOffset();"'
            ]
        ,   'toggle' => [
                'icon'              => 'visible.svg'
            ,   'attributes'        => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"'
            ]
        ,   'show' => [
                'href'              => 'act=show'
            ,   'icon'              => 'show.svg'
            ]
        ]
    ]
,   'palettes' => [
        'default'           => '{common_legend},code;{publish_legend},published,claimed'
    ]
,   'fields' => [
        'id' => [
            'sql'           => "int(10) unsigned NOT NULL auto_increment"
        ]
    ,   'pid' => [
            'foreignKey'    => FormModel::getTable().'.title'
        ,   'sql'           => "int(10) unsigned NOT NULL default 0"
        ,   'relation'      => ['type'=>'belongsTo', 'load'=>'lazy']
        ]
    ,   'tstamp' => [
            'flag'          => 8
        ,   'sql'           => "int(10) unsigned NOT NULL deftl_userault 0"
        ]
    ,   'code' => [
            'inputType'     => 'text'
        ,   'search'        => true
        ,   'eval'          => ['mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50']
        ,   'sql'           => "varchar(255) NOT NULL default ''"
        ]
    ,   'published' => [
            'exclude'       => true
        ,   'inputType'     => 'checkbox'
        ,   'filter'        => true
        ,   'eval'          => ['doNotCopy'=>true, 'tl_class'=>'w50']
        ,   'sql'           => "char(1) NOT NULL default ''"
        ]
    ,   'claimed' => [
            'exclude'       => true
        ,   'inputType'     => 'checkbox'
        ,   'filter'        => true
        ,   'eval'          => ['doNotCopy'=>true, 'disabled'=>true, 'tl_class'=>'w50']
        ,   'sql'           => "char(1) NOT NULL default ''"
        ]
    ,   'member' => [
            'foreignKey'    => MemberModel::getTable().".(CONCAT(firstname, ' ', lastname, ' [', id, ']'))"
        ,   'filter'        => true
        ,   'sql'           => "int(10) unsigned NOT NULL default 0"
        ,   'relation'      => ['type'=>'belongsTo', 'load'=>'lazy']
        ]
    ,   'form_data' => [
            'sql'           => "blob NULL"
        ]
    ]
];