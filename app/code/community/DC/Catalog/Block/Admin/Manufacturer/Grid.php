<?php
/**
 * Dot Collective - Magento Output 2009
 * Find more about Attribute Info Pages:
 * http://dot.collective.ro/magento-output/magento-shop-by-manufacturer-brand-character-attribute-info-pages/
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   DC
 * @package    DC_Catalog
 * @copyright  Copyright (c) 2009 Dot Collective SRL http://dot.collective.ro
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DC_Catalog_Block_Admin_Manufacturer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('manufacturerGrid');
        $this->setDefaultSort('attribute');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('dc_catalog/manufacturer')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        /*$this->addColumn('attribute_option_id', array(
            'header'    => Mage::helper('dc_catalog')->__('Option ID'),
            'align'     => 'left',
            'index'     => 'attribute_option_id',
        ));*/

        $res = Mage::getResourceModel('dc_catalog/manufacturer');
        $allowedAttributes = $res->getAllowedAttributes();

        $this->addColumn('name', array(
            'header'    => Mage::helper('dc_catalog')->__('Page Name'),
            'align'     => 'left',
            'index'     => 'name',
            'renderer'  => 'dc_catalog/admin_manufacturer_grid_renderer_name',
        ));

        $this->addColumn('attribute_code', array(
            'header'    => Mage::helper('dc_catalog')->__('Attribute'),
            'align'     => 'left',
            'index'     => 'attribute_code',
            'filter_index' => 'a.attribute_code',
            'type'  => 'options',
            'options' => $allowedAttributes,
        ));

        $this->addColumn('value', array(
            'header'    => Mage::helper('dc_catalog')->__('Attribute value'),
            'align'     => 'left',
            'index'     => 'value',
        ));

        /**
         * Check is single store mode
         */
         if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('dc_catalog')->__('Attribute in store'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('identifier', array(
            'header'    => Mage::helper('dc_catalog')->__('Identifier'),
            'align'     => 'left',
            'index'     => 'identifier',
        ));



        $this->addColumn('root_template', array(
            'header'    => Mage::helper('dc_catalog')->__('Layout'),
            'index'     => 'root_template',
            'type'      => 'options',
            'options'   => Mage::getSingleton('page/source_layout')->getOptions(),
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('cms')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('cms')->__('Disabled'),
                1 => Mage::helper('cms')->__('Enabled')
            ),
        ));

        $this->addColumn('creation_time', array(
            'header'    => Mage::helper('dc_catalog')->__('Date Created'),
            'index'     => 'creation_time',
            'type'      => 'datetime',
        ));

        /*$this->addColumn('attr_actions', array(
            'header'    => Mage::helper('dc_catalog')->__('Action'),
            'width'     => 10,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'dc_catalog/admin_manufacturer_grid_renderer_action',
        ));*/

        return parent::_prepareColumns();
    }

    /**
     * Mass action: set the Status of many values at once, in the default page or one store view.
     * The grid lists a value once per store view it has a label in, so the selection is by option id.
     */
    protected function _prepareMassaction()
    {
        $helper = Mage::helper('dc_catalog');

        $this->setMassactionIdField('option_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);   // the rows' own id is the false_id of the grid
        $this->getMassactionBlock()->setFormFieldName('option_ids');

        $storeOptions = array(array('value' => '0', 'label' => $helper->__('Default page (all store views that inherit)')));
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                foreach ($group->getStores() as $store) {
                    $storeOptions[] = array(
                        'value' => (string)$store->getId(),
                        'label' => $website->getName() . ' / ' . $store->getName(),
                    );
                }
            }
        }

        $this->getMassactionBlock()->addItem('status', array(
            'label'      => $helper->__('Change Status'),
            'url'        => $this->getUrl('*/*/massStatus'),
            'additional' => array(
                'store' => array(
                    'name'   => 'store',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $helper->__('Store view'),
                    'values' => $storeOptions,
                ),
                'status' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $helper->__('Status'),
                    'values' => array(
                        array('value' => '1',       'label' => $helper->__('Enabled')),
                        array('value' => '0',       'label' => $helper->__('Disabled')),
                        array('value' => 'default', 'label' => $helper->__('Use Default Value (store views only)')),
                    ),
                ),
            ),
        ));

        return $this;
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        if ($page_id = $row->getData('attribute_page_id')) {
            return $this->getUrl('*/*/edit', array('attribute_page_id' => $page_id));
        }
        return $this->getUrl('*/*/edit', array(
        	'attribute_code' => $row->getData('attribute_code'),
        	'option_id' => $row->getData('option_id'),
        	'store_id' => $row->getData('store_id')
		));

    }
}
