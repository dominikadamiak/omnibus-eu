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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Omnibus\Compatibility\PrestashopCompatibility;

class OmnibusEuFree extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'omnibuseufree';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.1';
        $this->author = 'presta.studio';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Omnibus Directive');
        $this->description = $this->l('This module will help you meet the requirements of the EU Omnibus Directive in your store.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module? The price history will be deleted.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        Configuration::updateValue('OMNIBUSEUFREE_INFORMATION_VERSION', 2);
        Configuration::updateValue('OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK', 1);
        Configuration::updateValue('OMNIBUSEUFREE_CRON_STATUS', 2);
        Configuration::updateValue('OMNIBUSEUFREE_DAYS', 2);

        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('actionProductAttributeUpdate') &&
            $this->registerHook('displayOmnibusEuFree') &&
            $this->registerHook('displayProductPriceBlock') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->installTab();
    }

    public function uninstall()
    {
        Configuration::deleteByName('OMNIBUSEUFREE_INFORMATION_VERSION');
        Configuration::deleteByName('OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK');
        Configuration::deleteByName('OMNIBUSEUFREE_CRON_STATUS');
        Configuration::deleteByName('OMNIBUSEUFREE_DAYS');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() &&
            $this->uninstallTab();
    }

    private function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('OmnibusEuFreeController');
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'OmnibusEuFreeController';
        $tab->route_name = 'admin_link_config';
        $tab->icon = 'euro_symbol';
        $tab->name = $this->l('Omnibus Directive');
        $tab->id_parent = (int) Tab::getIdFromClassName('IMPROVE');
        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('OmnibusEuFreeController');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    protected function getLastMinimalPrice($id_product = null, $id_product_attribute = 0, $currency = null)
    {
        if (!isset($id_product)) {
            throw new Exception('Missing parameter: $id_product');
        }

        if (!isset($currency)) {
            $defaultCurrency = Currency::getDefaultCurrency();
            $currency =  $defaultCurrency->id;
        }

        $info_version = Configuration::get('OMNIBUSEUFREE_INFORMATION_VERSION', null, null, null, 2);
        $NumberOfDays = (int) Configuration::get('OMNIBUSEUFREE_DAYS', null, null, null, 30);

        $date = new DateTime();
        $date->modify('-' . $NumberOfDays . ' days');
        $CutOffDate = $date->format('Y-m-d');

        $sql = new DbQuery();
        $sql->select('price');
        $sql->from('omnibus_eu_free');
        $sql->where('id_product = ' . (int) $id_product);
        $sql->where('id_product_attribute = ' . (int) $id_product_attribute);
        $sql->where('id_currency = ' . (int) $currency);
        $sql->where('date_add >= "'.$CutOffDate.' 00:00:00"');

        if ($info_version == 2) {
            $sql->where('is_last = 0');
        }

        $sql->orderBy('price ASC');
        $sql->limit(1);
        $sqlResult = Db::getInstance()->executeS($sql);

        return (!empty($sqlResult)) ? $sqlResult[0] : array();
    }

    public function hookDisplayOmnibusEuFree($params)
    {
        $currency = $this->context->currency;
        $lastMinimalPrice = $this->getLastMinimalPrice($params['product']['id_product'], $params['product']['id_product_attribute'], $currency->id);

        if (!empty($lastMinimalPrice)) {
            $minimalPrice = PrestashopCompatibility::formatPrice(_PS_VERSION_, $lastMinimalPrice['price'], $currency);
        } 
        else {
            $minimalPrice = null;
        }

        $this->context->smarty->assign([
            'OmnibuseufreeInfoVersion' => (int) Configuration::get('OMNIBUSEUFREE_INFORMATION_VERSION', null, null, null, 2),
            'OmnibuseufreeProductPriceMin' => $minimalPrice,
            'OmnibuseufreeProductPriceCurrent' => $params['product']['price'],
            'OmnibuseufreeProductDiscount' => (bool) $params['product']['has_discount'],
            'OmnibuseufreeNumberOfDays' => (int) Configuration::get('OMNIBUSEUFREE_DAYS', null, null, null, 30),
        ]);

        return $this->display(__FILE__, '/views/templates/hook/presta_studio_omnibus_price.tpl');
    }

    public function hookDisplayProductPriceBlock($params)
    {
        return ($params['type'] == 'after_price' && Configuration::get('OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK') == 1) ? $this->hookDisplayOmnibusEuFree($params) : '';
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $OmnibusData = array();

        foreach ($this->getOmnibusData($params['id_product']) as $rowId => $rowValue) {
            $OmnibusData[$rowId]['price_locale'] = '';
            $OmnibusData[$rowId]['currency_iso_code'] = '';
            $OmnibusData[$rowId]['name'] = '';
            $OmnibusData[$rowId]['is_last_icon'] = '';

            foreach ($rowValue as $key => $value) {
                if (isset($OmnibusData[$rowId]['price']) && $key == 'id_currency') {
                    $currency = Currency::getCurrencyInstance((int) $value);
                    $OmnibusData[$rowId]['price_locale'] = PrestashopCompatibility::formatPrice(_PS_VERSION_, $OmnibusData[$rowId]['price'], $currency);
                    $OmnibusData[$rowId]['currency_iso_code'] = $currency->iso_code;
                } 
                elseif ($key == 'id_product_attribute') {
                    $product = new Product($params['id_product']);
                    $OmnibusData[$rowId]['name'] = $product->getProductName($params['id_product'], $value);
                    ($product->hasCombinations() && $value == 0) ? $OmnibusData[$rowId]['name']  .= ' ' . $this->l('(default combination)') : '';
                } 
                elseif ($key == 'is_last' && $value == 1) {
                    $OmnibusData[$rowId]['is_last_icon'] = '<span class="material-icons text-success">done</span>';
                }

                $OmnibusData[$rowId][$key] = $value;
            }
        }

        $this->context->smarty->assign([
            'OmnibuseufreeData' => $OmnibusData
        ]);

        return $this->display(__FILE__, '/views/templates/admin/products-price-list.tpl');
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path . '/views/css/omnibuseufree-presta-studio.css');
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');
    }

    public function hookActionProductSave($hook_params)
    {
        if (Module::isEnabled('omnibuseufree')) {
            Product::flushPriceCache();

            $this->addProductPriceWithCombinations($hook_params['id_product']);
            $this->addProductPrice($hook_params['id_product']);
        }
    }

    public function hookActionProductAttributeUpdate()
    {
        if (Module::isEnabled('omnibuseufree')) {
            $id_product = (int) Tools::getValue('id_product');
            Product::flushPriceCache();

            $this->addProductPriceWithCombinations($id_product);
            $this->addProductPrice($id_product);
        }
    }

    /**
     * Load the configuration form
    */
    public function getContent()
    {
        $confirmation = '';
        if (((bool) Tools::isSubmit('submitOmnibusEuFreeModule')) == true) {
            $confirmation = $this->postProcess();
        }

        $token = Configuration::get('OMNIBUSEUFREE_CRON_TOKEN', null, null, null, 'error');

        $this->context->smarty->assign([
            'module_dir' => $this->_path,
            'lang' => $this->context->language->iso_code,
            'update_prices' => Context::getContext()->link->getModuleLink('omnibuseufree', 'cron', array('type' => 1, 'token' => $token)),
            'delete_outdated_data' => Context::getContext()->link->getModuleLink('omnibuseufree', 'cron', array('type' => 2, 'token' => $token)),
            'cron_status' => Configuration::get('OMNIBUSEUFREE_CRON_STATUS', null, null, null, 2)
        ]);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $confirmation . $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitOmnibusEuFreeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        $NumberOfDays = (int) Configuration::get('OMNIBUSEUFREE_DAYS', null, null, null, 30);

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Delete outdated data'),
                        'name' => 'OMNIBUSEUFREE_OLD_DATA',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Update prices'),
                        'name' => 'OMNIBUSEUFREE_UPDATE_DATABASE_PRICE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Version:'),
                        'name' => 'OMNIBUSEUFREE_INFORMATION_VERSION',
                        'class'    => 'omnibus-select-version',
                        'required' => true, 
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 1,
                                    'name' => sprintf($this->l('Lowest price in %d days'),$NumberOfDays)
                                ),
                                array(
                                    'id_option' => 2,
                                    'name' => sprintf($this->l('Lowest price in %d days before discount'),$NumberOfDays)
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type'     => 'text',                            
                        'label'    => $this->l('Number of days'),                   
                        'name'     => 'OMNIBUSEUFREE_DAYS',
                        'class'    => 'omnibus-input-days',
                        'maxlength'    => '3',
                        'required' => true
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display on product page'),
                        'name' => 'OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK',
                        'is_bool' => false,
                        'desc' => $this->l('Hook: ProductPriceBlock type: after_price'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 2,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('CRON'),
                        'name' => 'OMNIBUSEUFREE_CRON_STATUS',
                        'is_bool' => false,
                        'desc' => $this->l('A new token will be generated if you change the CRON status'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 2,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     * 
     * OMNIBUSEUFREE_CRON_STATUS since v1.0.2 
     * OMNIBUSEUFREE_DAYS since v1.0.2 
     * 
     */
    protected function getConfigFormValues()
    {
        return array(
            'OMNIBUSEUFREE_OLD_DATA' => false,
            'OMNIBUSEUFREE_UPDATE_DATABASE_PRICE' => false,
            'OMNIBUSEUFREE_INFORMATION_VERSION' => Configuration::get('OMNIBUSEUFREE_INFORMATION_VERSION', null, null, null, 2),
            'OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK' => Configuration::get('OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK', null, null, null, 1),
            'OMNIBUSEUFREE_CRON_STATUS' => Configuration::get('OMNIBUSEUFREE_CRON_STATUS', null, null, null, 2),
            'OMNIBUSEUFREE_DAYS' => Configuration::get('OMNIBUSEUFREE_DAYS', null, null, null, 30)
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Configuration::get('OMNIBUSEUFREE_CRON_STATUS', null, null, null, 2) != Tools::getValue('OMNIBUSEUFREE_CRON_STATUS')) {
            Configuration::updateValue('OMNIBUSEUFREE_CRON_TOKEN', Tools::hash(Tools::passwdGen(16)));
        }
        
        Configuration::updateValue('OMNIBUSEUFREE_INFORMATION_VERSION', (int) Tools::getValue('OMNIBUSEUFREE_INFORMATION_VERSION'));
        Configuration::updateValue('OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK', (int) Tools::getValue('OMNIBUSEUFREE_DISPLAY_PRODUCT_PRICE_BLOCK'));
        Configuration::updateValue('OMNIBUSEUFREE_CRON_STATUS', (int) Tools::getValue('OMNIBUSEUFREE_CRON_STATUS'));
        Configuration::updateValue('OMNIBUSEUFREE_DAYS', (int) Tools::getValue('OMNIBUSEUFREE_DAYS'));

        $confirmation = $this->l('The settings have been updated.');

        if (Tools::getValue('OMNIBUSEUFREE_OLD_DATA')) {
            $OldDataCounter = $this->removeOldDataFromOmnibusTable();
            PrestaShopLogger::addLog('Omnibus Directive module by presta.studio - Deleted old data. Number of records deleted in database: ' . $OldDataCounter);
            $confirmation .= '<br>' . $this->l('Deleted old data. Number of records deleted in database: ') . $OldDataCounter;
        }

        if (Tools::getValue('OMNIBUSEUFREE_UPDATE_DATABASE_PRICE')) {
            $InsertDataCounter = $this->insertAllProductsToOmnibusTable();
            PrestaShopLogger::addLog('Omnibus Directive module by presta.studio - Updated price history. Number of products checked: ' . $InsertDataCounter);
            $confirmation .= '<br>' . $this->l('Updated price history. Number of products checked: ') . $InsertDataCounter;
        }

        return $this->displayConfirmation($confirmation);
    }

    protected function checkCurrencyConversionRate($id_product = null, $id_product_attribute = 0)
    {
        if (!isset($id_product)) {
            throw new Exception('Missing parameter: $id_product');
        }

        $sql = new DbQuery();
        $sql->select('id_currency, currency_conversion_rate');
        $sql->from('omnibus_eu_free');
        $sql->where('id_product = ' . (int) $id_product);
        $sql->where('id_product_attribute = ' . (int) $id_product_attribute);
        $sql->where('is_last = 1');
        $SQLResult = Db::getInstance()->executeS($sql);

        $ToChange = 0;
        $OmnibusCurrencies = array();

        foreach ($SQLResult as $row) {
            $OmnibusCurrencies[$row['id_currency']] = $row['currency_conversion_rate'];
        }

        if (!empty($OmnibusCurrencies)) {
            $currencies = Currency::getCurrencies();
            $AvailableCurrencies = array();

            foreach ($currencies as $currency) {
                $AvailableCurrencies[$currency['id_currency']] = $currency['conversion_rate'];
            }

            foreach ($AvailableCurrencies as $CurrencyKey => $CurrencyValue) {
                if (!isset($OmnibusCurrencies[$CurrencyKey]) || $CurrencyValue != $OmnibusCurrencies[$CurrencyKey]) {
                    $ToChange++;
                }
            }
        } 
        else {
            $ToChange++;
        }

        return $ToChange == 0 ? false : true;
    }

    protected function addProductPrice($id_product = null)
    {
        if (!isset($id_product)) {
            throw new Exception('Missing parameter: $id_product');
        }

        $product = new Product($id_product);
        $product_price = $product->getPrice();

        if($product_price != 0){
            $omnibus_price = array();
            $omnibus_price = $this->getLastPrice($id_product, 0);

            $check_currency = $this->checkCurrencyConversionRate($id_product, 0);

            if (empty($omnibus_price) || $product_price != $omnibus_price[0]['price'] || (bool) $check_currency == true) {
                $this->clearLastPrice($id_product);
                $currencies = Currency::getCurrencies();
                $defaultCurrency = Currency::getDefaultCurrency();

                foreach ($currencies as $currency) {
                    if ($currency['id_currency'] == $defaultCurrency->id) {
                        $isDefaultCurrency = 1;
                        $product_price_currency = $product_price;
                    } 
                    else {
                        $isDefaultCurrency = 0;
                        $product_price_currency = Tools::convertPrice($product_price, $currency);
                    }

                    $this->insertToOmnibusTable($id_product, 0, $product_price_currency, $currency['id_currency'], $isDefaultCurrency, $currency['conversion_rate']);
                }
            }
        }
    }

    protected function addProductPriceWithCombinations($id_product = null)
    {
        if (!isset($id_product)) {
            throw new Exception('Missing parameter: $id_product');
        }

        $product = new Product($id_product);
        $product_combinations = $product->getAttributeCombinations();

        if (!empty($product_combinations)) {
            $currencies = Currency::getCurrencies();
            $defaultCurrency = Currency::getDefaultCurrency();

            foreach ($product_combinations as $combination) {
                $product_price = $product->getPrice(true, $combination['id_product_attribute']);

                if($product_price != 0){
                    $omnibus_price = array();
                    $omnibus_price = $this->getLastPrice($id_product, $combination['id_product_attribute']);

                    $check_currency = $this->checkCurrencyConversionRate($id_product, $combination['id_product_attribute']);

                    if (empty($omnibus_price) || $product_price != $omnibus_price[0]['price'] || (bool) $check_currency == true) {
                        $this->clearLastPrice($id_product, $combination['id_product_attribute']);

                        foreach ($currencies as $currency) {
                            if ($currency['id_currency'] == $defaultCurrency->id) {
                                $isDefaultCurrency = 1;
                                $product_price_currency = $product_price;
                            } 
                            else {
                                $isDefaultCurrency = 0;
                                $product_price_currency = Tools::convertPrice($product_price, $currency);
                            }

                            $this->insertToOmnibusTable($id_product, $combination['id_product_attribute'], $product_price_currency, $currency['id_currency'], $isDefaultCurrency, $currency['conversion_rate']);
                        }
                    }
                }
            }
        }
    }

    protected function insertToOmnibusTable($id_product = 0, $id_product_attribute = 0, $product_price = 0, $currency = 0, $isDefaultCurrency = 0, $CurrencyConversionRate = 0)
    {
        $result = Db::getInstance()->insert('omnibus_eu_free', [
            'id_product' => (int) $id_product,
            'id_product_attribute' => (int) $id_product_attribute,
            'price' => pSQL($product_price),
            'id_currency' => (int) $currency,
            'is_default_currency' => (int) $isDefaultCurrency,
            'is_last' => 1,
            'currency_conversion_rate' => pSQL($CurrencyConversionRate)
        ]);
    }

    protected function getLastPrice($id_product = 0, $id_product_attribute = 0)
    {
        $sql = new DbQuery();
        $sql->select('price');
        $sql->from('omnibus_eu_free');
        $sql->where('id_product = ' . (int) $id_product);
        $sql->where('id_product_attribute = ' . (int) $id_product_attribute);
        $sql->where('is_last = 1');
        $sql->where('is_default_currency = 1');
        $sql->limit(1);

        return Db::getInstance()->executeS($sql);
    }

    protected function getOmnibusData($id_product = null)
    {
        if (!isset($id_product)) {
            throw new Exception('Missing parameter: $id_product');
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('omnibus_eu_free');
        $sql->where('id_product = ' . (int) $id_product);
        $sql->orderBy('is_default_currency DESC');
        $sql->orderBy('id_currency ASC');
        $sql->orderBy('id_product_attribute ASC');
        $sql->orderBy('id_omnibuseufree DESC');

        return Db::getInstance()->executeS($sql);
    }

    protected function clearLastPrice($id_product = 0, $id_product_attribute = 0)
    {
        $result = Db::getInstance()->update('omnibus_eu_free', array(
            'is_last' => 0,
        ), 'id_product = ' . (int) $id_product . ' AND id_product_attribute = ' . (int) $id_product_attribute);
    }

    public function insertAllProductsToOmnibusTable()
    {
        $counter = 0;
        $ProductClass = new Product();
        $ProductClass->flushPriceCache();
        $products = $ProductClass->getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC');

        foreach ($products as $product) {
            $this->addProductPriceWithCombinations($product['id_product']);
            $this->addProductPrice($product['id_product']);
            $counter++;
        }

        return $counter;
    }

    public function removeOldDataFromOmnibusTable()
    {
        $NumberOfDays = (int) Configuration::get('OMNIBUSEUFREE_DAYS', null, null, null, 30);

        $date = new DateTime();
        $date->modify('-' . $NumberOfDays . ' days');
        $CutOffDate = $date->format('U');
        $counter = 0;

        $sql = new DbQuery();
        $sql->select('id_omnibuseufree, date_add');
        $sql->from('omnibus_eu_free');
        $sql->where('is_last = 0');
        $result = Db::getInstance()->executeS($sql);

        foreach ($result as $row) {
            $date = new DateTime($row['date_add']);
            $DatabaseDate = $date->format('U');

            if ($DatabaseDate < $CutOffDate) {
                Db::getInstance()->delete('omnibus_eu_free', '`id_omnibuseufree` = ' . (int) $row['id_omnibuseufree']);
                $counter++;
            }
        }

        return $counter;
    }
}
