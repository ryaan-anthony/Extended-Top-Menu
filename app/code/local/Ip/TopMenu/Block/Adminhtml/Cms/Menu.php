<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cms page edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Ip_TopMenu_Block_Adminhtml_Cms_Menu
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        /* @var $model Mage_Cms_Model_Page */
        $model = Mage::registry('cms_page');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }


        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('cms')->__('Page Information')));

        if ($model->getPageId()) {
            $fieldset->addField('page_id', 'hidden', array(
                'name' => 'page_id',
            ));
        }

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('cms')->__('Page Title'),
            'title'     => Mage::helper('cms')->__('Page Title'),
            'required'  => true,
            'disabled'  => $isElementDisabled
        ));

        $fieldset->addField('identifier', 'text', array(
            'name'      => 'identifier',
            'label'     => Mage::helper('cms')->__('URL Key'),
            'title'     => Mage::helper('cms')->__('URL Key'),
            'required'  => true,
            'class'     => 'validate-identifier',
            'note'      => Mage::helper('cms')->__('Relative to Website Base URL'),
            'disabled'  => $isElementDisabled
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                'disabled'  => $isElementDisabled,
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('cms')->__('Status'),
            'title'     => Mage::helper('cms')->__('Page Status'),
            'name'      => 'is_active',
            'required'  => true,
            'options'   => $model->getAvailableStatuses(),
            'disabled'  => $isElementDisabled,
        ));



        //Menu Magic starts here...
        $include_in_nav = $fieldset->addField('include_in_nav', 'select', array(
            'name'      => 'include_in_nav',
            'label'     => Mage::helper('cms')->__('Include in Navigation Menu'),
            'title'     => Mage::helper('cms')->__('Include in Navigation Menu'),
            'required'  => true,
            'options'   => array(1=>'Yes', 0=>'No'),
            'disabled'  => $isElementDisabled
        ));
        $include_in_nav_position = $fieldset->addField('include_in_nav_position', 'text', array(
            'name'      => 'include_in_nav_position',
            'label'     => Mage::helper('cms')->__('Nav Menu Position'),
            'title'     => Mage::helper('cms')->__('Nav Menu Position'),
            'required'  => false,
            'disabled'  => $isElementDisabled
        ));
        $include_in_nav_label = $fieldset->addField('include_in_nav_label', 'text', array(
            'name'      => 'include_in_nav_label',
            'label'     => Mage::helper('cms')->__('Nav Menu Label'),
            'title'     => Mage::helper('cms')->__('Nav Menu Label'),
            'required'  => false,
            'disabled'  => $isElementDisabled
        ));
        $include_in_nav_parent = $fieldset->addField('include_in_nav_parent', 'select', array(
            'name'      => 'include_in_nav_parent',
            'label'     => Mage::helper('cms')->__('Parent'),
            'title'     => Mage::helper('cms')->__('Parent'),
            'required'  => false,
            'options'   => $this->getCmsPages(),
            'disabled'  => $isElementDisabled
        ));
        //Menu Magic ends here...




        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        Mage::dispatchEvent('adminhtml_cms_page_edit_tab_main_prepare_form', array('form' => $form));

        $form->setValues($model->getData());
        $this->setForm($form);

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($include_in_nav->getHtmlId(), $include_in_nav->getName())
                ->addFieldMap($include_in_nav_label->getHtmlId(), $include_in_nav_label->getName())
                ->addFieldMap($include_in_nav_parent->getHtmlId(), $include_in_nav_parent->getName())
                ->addFieldMap($include_in_nav_position->getHtmlId(), $include_in_nav_position->getName())
                ->addFieldDependence(
                    $include_in_nav_label->getName(),
                    $include_in_nav->getName(),
                    1
                )
                ->addFieldDependence(
                    $include_in_nav_parent->getName(),
                    $include_in_nav->getName(),
                    1
                )
                ->addFieldDependence(
                    $include_in_nav_position->getName(),
                    $include_in_nav->getName(),
                    1
                )
        );



        return parent::_prepareForm();
    }



    protected function getCmsPages()
    {
        $results = array( 0 => 'None' );
        $collection = Mage::getModel('cms/page')->getCollection();
        foreach($collection as $item){
            $results[$item->getId()] = $item->getTitle();
        }
        return $results;
    }
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('cms')->__('Page Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('cms')->__('Page Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/page/' . $action);
    }

}
