<?php
/**
*
* NOTICE OF LICENSE
*
* This source file is subject to the Apache License, Version 2.0
* that is bundled with this package in the file LICENSE.
* It is also available through the world-wide-web at this URL:
* http://www.apache.org/licenses/LICENSE-2.0
*
*  @author    presta.studio
*  @copyright Copyright (c) 2023 presta.studio
*  @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
*/

namespace Omnibus\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Tools;
use Context;

class MenuController extends FrameworkBundleAdminController
{
    public function menuAction()
    {
        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules').'&configure=omnibuseufree');
    }
}