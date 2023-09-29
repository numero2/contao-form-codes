<?php

/**
 * Form codes bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2023, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\FormCodesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;


class FormCodesBundle extends Bundle {

    public function getPath(): string {
        return \dirname(__DIR__);
    }
}