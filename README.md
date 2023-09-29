Contao Form Codes
=======================

[![](https://img.shields.io/packagist/v/numero2/contao-form-codes.svg?style=flat-square)](https://packagist.org/packages/numero2/contao-form-codes) [![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg?style=flat-square)](http://www.gnu.org/licenses/lgpl-3.0)

About
--

This extension allows you to include a code within the notification when sending a form to your users. This code can be, for instance, a voucher, discount, or invitation code. Each code is valid only once, and you can track who has claimed which codes.

System requirements
--

* [Contao 4.13](https://github.com/contao/contao) (or newer)
* [terminal42/contao-notification_center](https://github.com/terminal42/contao-notification_center)

Installation
--

* Install via Contao Manager or Composer (`composer require numero2/contao-form-codes`)
* Run a database update via the Contao-Installtool or using the [contao:migrate](https://docs.contao.org/dev/reference/commands/) command.
* Create a new notification in the `Notification Center` of type `Contao: Form submission` and use the now available Simple-Token `##fc_code##` to put the generated code into your e-mail
* Create or edit a form and check the option `Für Codes benutzen?`, also make sure to select the correct notification
* In the overview of your forms you'll now be presented with an additional button where you can import and manage your codes
  
  