<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\FormCodesBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\FormModel;
use Contao\Image;
use Contao\StringUtil;


class FormListener {


    /**
     * Modifies the palettes of the current DataContainer
     *
     * @param Contao\DataContainer $dc
     *
     * @Callback(table="tl_form", target="config.onload")
     */
    public function modifyPalettes( DataContainer $dc ): void {

        PaletteManipulator::create()
            ->addLegend('form_codes_legend', 'email_legend', PaletteManipulator::POSITION_BEFORE)
            ->addField(['has_form_codes'], 'form_codes_legend', PaletteManipulator::POSITION_APPEND )
            ->applyToPalette('default', $dc->table);
    }


    /**
     * Hides the codes button if applicable
     *
     * @param array $row
     * @param string|null $href
     * @param string $label
     * @param string $title
     * @param string|null $icon
     * @param string $attributes
     * @param string $table
     * @param array $rootRecordIds
     * @param array|null $childRecordIds
     * @param bool $circularReference
     * @param string|null $previous
     * @param string|null $next
     * @param Contao\DataContainer $dc
     *
     * @return string
     * 
     * @Callback(table="tl_form", target="list.operations.codes.button")
     */
    public function hideCodesButton(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes, string $table, array $rootRecordIds, ?array $childRecordIds, bool $circularReference, ?string $previous, ?string $next, DataContainer $dc): string {

        if( !$row['has_form_codes'] ) {
            return '';
        }

        $user = BackendUser::getInstance();

        if( !$user->isAdmin ) {

            if( !$user->hasAccess('form_codes', 'formp') ) {

                $icon = str_replace('.svg','_.svg',$icon);
                return '<span>'.Image::getHtml($icon, $label, '').'</span> ';
            }
        }

        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> '
        ,   Backend::addToUrl($href . '&amp;id=' . $row['id'])
        ,   StringUtil::specialchars($title)
        ,   $attributes
        ,   Image::getHtml($icon, $label)
        );
    }


    /**
     * Make the notification field mandatory if necessary
     *
     * @param Contao\DataContainer|null $dc
     * 
     * @Callback(table="tl_form", target="config.onload")
     */
    public function makeNotificationMandatory(?DataContainer $dc): void {

        if( $dc && $dc->id ) {

            $model = null;
            $model = FormModel::findById($dc->id);

            if( $model && $model->has_form_codes ) {
                $GLOBALS['TL_DCA'][$dc->table]['fields']['nc_notification']['eval']['mandatory'] = true;        
            }
        }
    }

}