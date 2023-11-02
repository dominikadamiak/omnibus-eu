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

<div class="panel">

	<div class="panel-heading">
		<div class="presta-studio-omnibus-header">
			<div class="presta-studio-omnibus-header-left">
				<i class="icon icon-tags"></i>{l s='Documentation' mod='omnibuseufree'}
			</div>
			<div class="presta-studio-omnibus-header-right">
				<a href="https://presta.studio" target="_blank"><img src="{$module_dir}views/img/presta_studio_logo.png" width="92" height="16" alt="presta.studio"></a>
				<a href="https://www.youtube.com/@presta.studio" target="_blank" class="follow-presta-studio"><img src="{$module_dir}views/img/yt_logo.png" width="71" height="16" alt="Follow us on YouTube"></a>
			</div>
		</div>
	</div>
	<div class="panel-body">
		<p>&raquo; {l s='Module installation and configuration' mod='omnibuseufree'}:</p>
		<ul class="presta-studio-documentation-list">
			<li><a href="https://presta.studio/omnibus-documentation-en" target="_blank">{l s='English' mod='omnibuseufree'}</a></li>
			<li><a href="https://presta.studio/omnibus-documentation-pl" target="_blank">{l s='Polish' mod='omnibuseufree'}</a></li>
		</ul>
		
		<p >&raquo; {l s='Follow us on' mod='omnibuseufree'}:</p>
		<ul>
			<li><a href="https://www.youtube.com/@presta.studio" target="_blank">YouTube</a></li>
			<li><a href="https://presta.studio/github" target="_blank">GitHub</a></li>
		</ul>
		
	</div>
</div>
<div class="panel">
	<div class="panel-body text-center">
		{if $lang == 'pl'}
			<a href="https://presta.studio/pl/" target="_blank"><img src="{$module_dir}views/img/baner_pl.png" width="750" height="100" class="presta-studio-ads"></a>
			<a href="https://presta.studio/pl/" target="_blank"><img src="{$module_dir}views/img/baner_mobile_pl.png" width="300" height="250" class="presta-studio-ads-mobile"></a>
		{else}
			<a href="https://presta.studio/" target="_blank"><img src="{$module_dir}views/img/baner_en.png" width="750" height="100" class="presta-studio-ads"></a>
			<a href="https://presta.studio/" target="_blank"><img src="{$module_dir}views/img/baner_mobile_en.png" width="300" height="250" class="presta-studio-ads-mobile"></a>
		{/if}
	</div>
</div>
