{*
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
*
*}

{if $OmnibuseufreeProductDiscount == true}
    <div class="presta-studio-price-history">
        <p class="presta-studio-price-history-text">
        {if $OmnibuseufreeInfoVersion == 2}
            {if $OmnibuseufreeProductPriceMin != null}
                {l s='Lowest price in 30 days before discount: ' mod='omnibuseufree'}<span class="presta-studio-price-history-price">{$OmnibuseufreeProductPriceMin}</span>
            {else}
                {* when history is empty *}
                {l s='Lowest price in 30 days: ' mod='omnibuseufree'}<span class="presta-studio-price-history-price">{$OmnibuseufreeProductPriceCurrent}</span>
            {/if}
        {else}
            {l s='Lowest price in 30 days: ' mod='omnibuseufree'}<span class="presta-studio-price-history-price">{$OmnibuseufreeProductPriceMin}</span>
        {/if}
        </p>
    </div>
{/if}