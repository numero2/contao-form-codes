<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\FormCodesBundle\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use numero2\FormCodesBundle\FormCodeModel;


class FormCodeListener {


    /**
     * @var Contao\Backend
     */
    private $backend;

    /**
     * @var Contao\CoreBundle\Framework\ContaoFramework
     */
    private $framework;

    /**
     * @var Doctrine\DBAL\Connection
     */
    private $connection;


    public function __construct( ContaoFramework $framework, Connection $connection ) {

        $this->framework = $framework;
        $this->connection = $connection;

        /** @var Contao\Backend $backend */
        $backend = $this->framework->getAdapter(Backend::class);
        $this->backend = $backend;
    }


    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @Callback(table="tl_form_code", target="list.operations.toggle.button")
     */
    public function toggleIcon( $row, $href, $label, $title, $icon, $attributes ): string {

        if( Input::get('tid') ) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (func_num_args() <= 12 ? null : func_get_arg(12)));
            Controller::redirect(System::getReferer());
        }

        $user = BackendUser::getInstance();

        // check permissions AFTER checking the tid, so hacking attempts are logged
        if( !$user->isAdmin ) {

            if( !$user->hasAccess(FormCodeModel::getTable().'::published', 'alexf') || $user->id != $row['in_charge'] ) {
                return '';
            }
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if( !$row['published'] ) {
            $icon = 'invisible.svg';
        }

        return '<a href="' . Controller::addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }


    /**
     * Disable/enable a code
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param Contao\DataContainer $dc
     */
    public function toggleVisibility( $intId, $blnVisible, DataContainer $dc=null ) {

        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if( $dc ) {
            $dc->id = $intId; // see #8043
        }

        // check the field access
        if( !BackendUser::getInstance()->hasAccess(FormCodeModel::getTable().'::published', 'alexf') ) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish form code ID ' . $intId . '.');
        }

        $stmt = $this->connection->prepare("SELECT * FROM ".FormCodeModel::getTable()." WHERE id=:id");
        $res = $stmt->execute(['id'=>$dc->id]);

        if( (method_exists($stmt, 'rowCount') && $stmt->rowCount() < 1) || (method_exists($res, 'rowCount') && $res->rowCount() < 1) ) {
            throw new AccessDeniedException('Invalid form code ID ' . $intId . '.');
        }

        // set the current record
        if( $dc ) {

            if( method_exists($stmt, 'fetchAssociative') ) {
                $dc->activeRecord = (object) $stmt->fetchAssociative();
            } else {
                $dc->activeRecord = (object) $res->fetchAssociative();
            }
        }

        $objVersions = new Versions(FormCodeModel::getTable(), $intId);
        $objVersions->initialize();

        $time = time();

        // update the database
        $stmt = $this->connection->prepare("UPDATE ".FormCodeModel::getTable()." SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=:id");
        $res = $stmt->execute(['id'=>$dc->id]);

        if( $dc ) {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        $objVersions->create();

        if($dc) {
            $dc->invalidateCacheTags();
        }
    }


    /**
     * Lists codes
     *
     * @param array $row
     * @param string $label
     * @param Contao\DataContainer $dc
     * @param array $labels
     *
     * @return array
     * 
     * @Callback(table="tl_form_code", target="list.label.label")
     */
    public function listCodes( array $row, string $label, DataContainer $dc, array $labels ): array {

        $labels[0] = '<span class="form_codes claimed '.($row['claimed']?'yes':'').'">' .$labels[0]. '</span>';
        $labels[1] = '<span class="form_codes code">' .$labels[1]. '</span>';

        if( $row['claimed'] && !$row['member'] ) {

            $data = [];
            $data = StringUtil::deserialize($row['form_data']);

            if( !empty($data['email']) ) {
                $labels[2] = $data['email'];
            }
        }

        return $labels;
    }


    /**
     * Format values for the "show" action
     *
     * @param array $modal
     * @param array $row
     * @param Contao\DataContainer $dc
     *
     * @return array
     * 
     * @Callback(table="tl_form_code", target="config.onshow")
     */
    public function show( array $modal, array $row, DataContainer $dc ): array {

        $t = FormCodeModel::getTable();

        if( !empty($modal[$t][0]) ) {

            foreach( $modal[$t][0] as $field => $value ) {

                // try to make form_data a bit more readable
                // unfortunately we can't use any HTML (see DC_Table.php#L604)
                if( stripos($field,'form_data') !== false ) {
                    
                    $value = '';

                    $data = [];
                    $data = StringUtil::deserialize($row['form_data']);
                    
                    if( !empty($data) ) {

                        foreach( $data as $k => $v ) {
                            $value .= "$k:\n$v\r\n\n";
                        }
                        
                        $modal[$t][0][$field] = $value;
                    }
                }
            }
        }

        return $modal;
    }


    /**
     * Check permissions to edit the table
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     *
     * @Callback(table="tl_form_code", target="config.onload")
     */
    public function checkPermissions( DataContainer $dc ): void {

        $id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;
        $user = BackendUser::getInstance();

        if( $user->isAdmin ) {
            return;
        }

        if( !$user->hasAccess('form_codes', 'formp') ) {
            
            $GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = true;

            // check current action
            if( Input::get('act') ) {
                throw new AccessDeniedException('No permission to "' . Input::get('act') . '" form codes.');
            }
        }
    }


    /**
     * Hide import button if table closed
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     *
     * @Callback(table="tl_form_code", target="config.onload")
     */
    public function hideImportButton( DataContainer $dc ): void {

        if( !empty($GLOBALS['TL_DCA'][$dc->table]['config']['closed']) ) {
            
            unset($GLOBALS['TL_DCA'][$dc->table]['list']['global_operations']['import']);
        }
    }
}