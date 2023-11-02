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

<div class="mb-4">
  <button type="button" class="btn btn-primary" aria-label="Iceberg" id="omnibuseu-by-presta-studio-reload">
    <i class="material-icons">rotate_right</i> {l s='Refresh page' mod='omnibuseufree'}
  </button> 
  <div class="small mt-1 font-weight-bold">
    {l s='Save changes before refresh page!' mod='omnibuseufree'}
  </div>
</div>

<table class="table table-hover">
  <thead>
    <tr>
      <th>{l s='ID: ' mod='omnibuseufree'}</th>
      <th>{l s='Product name: ' mod='omnibuseufree'}</th>
      <th>{l s='Price: ' mod='omnibuseufree'}</th>
      <th>{l s='Currency: ' mod='omnibuseufree'}</th>
      <th>{l s='Last value: ' mod='omnibuseufree'}</th>
      <th>{l s='Date Added: ' mod='omnibuseufree'}</th>
    </tr>
  </thead>
  <tbody>
  {if !empty($OmnibuseufreeData)}
    {foreach from=$OmnibuseufreeData item=item key=key}
      <tr>
          <th scope="row">{$key+1}</th>
          <td>{$item.name}</td>
          <td>{$item.price_locale}</td>
          <td>{$item.currency_iso_code}</td>
          <td>{$item.is_last_icon}</td>
          <td>{$item.date_add}</td>
      </tr>
    {/foreach}
  {/if}
  </tbody>
</table>