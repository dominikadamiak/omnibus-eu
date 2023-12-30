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

declare(strict_types=1);

namespace Omnibus\Compatibility;

use Tools;
use Context;

class PrestashopCompatibility
{
    public static function formatPrice($PSversion, $price, $currency)
    {
        if (version_compare($PSversion, '1.7.7') >= 0) {
            return Tools::getContextLocale(Context::getContext())->formatPrice($price, $currency->iso_code);
        } 
        else {
            return Tools::displayPrice($price, $currency->id);
        }
    }
}
