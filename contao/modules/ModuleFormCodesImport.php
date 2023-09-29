<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\FormCodesBundle;

use \Exception;
use Contao\BackendTemplate;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Contao\TextArea;
use numero2\FormCodesBundle\FormCodeModel;
use Symfony\Contracts\Translation\TranslatorInterface;


class ModuleFormCodesImport {


    /**
     * @var Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;


    public function __construct() {

        $this->translator = System::getContainer()->get('translator');
    }


    public function renderForm(): string {

        $id = Input::get('id');
        $do = Input::get('do');
        $table = Input::get('table');
        
        $ref = System::getContainer()->get('request_stack')->getCurrentRequest()->get('_contao_referer_id');
        $routePrefix = System::getContainer()->getParameter('contao.backend.route_prefix');
        $requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        $formSubmit = 'tl_form_code';

        $template = new BackendTemplate('be_form_code_import');

        $template->hrefBack = $routePrefix . '?do=' .$do. '&amp;table=' .$table. '&amp;id=' .$id. '&amp;rt=' .$requestToken. '&amp;ref=' .$ref;;
        $template->formSubmit = $formSubmit;
        $template->requestToken = $requestToken;

        $widget = new TextArea();
        $widget->name = 'codes';
        $widget->mandatory = true;
        $widget->label = $this->translator->trans('tl_form_code.codes.0', [], 'contao_default');
        $template->widgetHelp = $this->translator->trans('tl_form_code.codes.1', [], 'contao_default');

        $template->widget = $widget->parse();

        // handle form submit
        if( Input::post('FORM_SUBMIT') == $formSubmit ) {
            
            $widget->validate();
            
            if( !$widget->hasErrors() ) {

                $numCodes = 0;
                $numCodes = $this->importCodes($id, $widget->value);

                if( $numCodes > 0 ) {
                    
                    Message::addInfo(
                        sprintf(
                            $this->translator->trans('MSC.numFormCodesImported', [], 'contao_default')
                        ,   $numCodes
                        )
                    );
                    
                } else {

                    Message::addError(
                        $this->translator->trans('ERR.noFormCodesImported', [], 'contao_default')
                    );
                }
            }
        }

        $template->message = Message::generate();

        return $template->parse();
    }


    /**
     * Splits the string of given codes and imports them individually
     *
     * @param int $pid
     * @param string $codes
     *
     * @return int|null Number of successfully imported codes
     */
    private function importCodes( int $pid, string $codes ): ?int {

        $aCodes = [];
        $aCodes = explode("\n", trim($codes));

        if( !empty($aCodes) ) {

            $numImported = 0;

            foreach( $aCodes as $code ) {

                if( empty($code)) {
                    continue;
                }

                $model = new FormCodeModel();
                
                $model->tstamp = time();
                $model->pid = $pid;
                $model->code = $code;
                $model->published = 1;

                try {

                    $model->save();
                    $numImported++;

                } catch( Exception $e ) {
                }
            }

            return $numImported;
        }

        return null;
    }

}