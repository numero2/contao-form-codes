<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\FormCodesBundle\EventListener\Hooks;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\System;
use Contao\Form;
use Contao\FormModel;
use Contao\FrontendUser;
use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Message;
use numero2\FormCodesBundle\FormCodeModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;



class FormListener {


    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface;
     */
    private $container;

    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;


    public function __construct( ContainerInterface $container, RequestStack $requestStack ) {

        $this->container = $container;
        $this->requestStack = $requestStack;
    }



    /**
     * Adds a code token if applicable
     *
     * @param NotificationCenter\Model\Message $message
     * @param array $arrTokens
     * @param string $language
     * @param NotificationCenter\Model\Gateway $gatweay
     *
     * @return bool
     * 
     * @Hook("processFormData", priority=128)
     */
    public function processFormData( $arrSubmitted, $config, $arrFiles, $arrLabels, Form $form ): bool {

        if( !$config['has_form_codes'] ) {
            return true;
        }

        // get available code
        $model = null;
        $model = FormCodeModel::findOneBy(['pid=?','published=?','claimed != 1'], [$config['id'],1]);

        if( !$model ) {

            System::log('No codes available for form ID '.$config['id'], __METHOD__, TL_ERROR);

        } else {

            // add form data to code
            $model->form_data = $arrSubmitted;
            $model->save();

            // store code in request for later use in sendNotificationMessage hook
            $request = $this->requestStack->getCurrentRequest();
            $request->attributes->set('fc_form_code', $model);
        }

        return true;
    }



    /**
     * Adds a code token if applicable
     *
     * @param NotificationCenter\Model\Message $message
     * @param array $arrTokens
     * @param string $language
     * @param NotificationCenter\Model\Gateway $gatweay
     *
     * @return bool
     * 
     * @Hook("sendNotificationMessage")
     */
    public function addCodeToken( Message $message, array &$arrTokens, $language='', Gateway $gatweay=null ): bool {

        if( empty($arrTokens['formconfig_id']) ) {
            return true;
        }

        // check if form is configured for use with codes
        $form = null;
        $form = FormModel::findById($arrTokens['formconfig_id']);

        if( !$form || !$form->has_form_codes ) {
            return true;
        }

        $request = $this->requestStack->getCurrentRequest();
        
        $model = null;
        $model = $request->attributes->get('fc_form_code');

        if( $model ) {

            $arrTokens['fc_code'] = $model->code;

            if( !$model->claimed ) {
    
                if( $this->container->get('contao.security.token_checker')->hasFrontendUser() ) {

                    $member = FrontendUser::getInstance();
                    $model->member = $member->id;
                }

                $model->claimed = 1;
                $model->tstamp = time();
                $model->save();
            }
        }

        return true;
    }
}