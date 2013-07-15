<?php

class Ip_TopMenu_Model_Catalog_Observer extends Mage_Catalog_Model_Observer
{


    protected function _addCategoriesToMenu($categories, $parentNode)
    {
        $menu_items = $this->getCategories($categories);
        $menu_items = array_merge($this->getCmsPages(),$menu_items);
        usort($menu_items, array($this, 'sortByPosition'));
        $this->addToMenu($menu_items, $parentNode);
    }

    protected function sortByPosition($a, $b)
    {
        if ($a['position'] == $b['position']) {
            return 0;
        }
        return ($a['position'] < $b['position']) ? -1 : 1;
    }

    protected function addToMenu($menu_items, $parentNode)
    {
        foreach($menu_items as $item){
            $children = $item['children'];
            unset($item['children']);
            $tree = $parentNode->getTree();
            $thisNode = new Varien_Data_Tree_Node($item, 'id', $tree, $parentNode);
            $parentNode->addChild($thisNode);
            $this->addToMenu($children, $thisNode);
        }
    }

    protected function getCmsPages($parent = null)
    {
        $results = array();
        $collection = Mage::getModel('cms/page')->getCollection();
        foreach($collection as $item){
            if(!$item->getData('include_in_nav')){continue;}
            if(($parent==null && !$item->getData('include_in_nav_parent'))|| $parent == $item->getData('include_in_nav_parent')){
                $url = Mage::getUrl($item->getData('identifier'));
                if($item->getData('identifier') == Mage::getStoreConfig('web/default/cms_home_page')){
                    $url = Mage::getUrl();
                }
                $name = $item->getData('include_in_nav_label') ? $item->getData('include_in_nav_label') : $item->getTitle();
                $results[] = array(
                    'name' => $name,
                    'position' => $item->getData('include_in_nav_position'),
                    'id' => 'cms-node-' . $item->getId(),
                    'url' => $url,
                    'is_active' => true,
                    'children' => $this->getCmsPages($item->getId())
                );
            }
        }
        return $results;
    }

    protected function getCategories($categories)
    {
        $results = array();
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            if (Mage::helper('catalog/category_flat')->isEnabled()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }
            $category = Mage::getModel('catalog/category')->load($category->getId());
            $position = $category->getNavPosition() ? $category->getNavPosition() : $category->getPosition();
            $results[] = array(
                'name' => $category->getName(),
                'position' => $position,
                'id' => 'category-node-' . $category->getId(),
                'url' => Mage::helper('catalog/category')->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category),
                'children' => $this->getCategories($subcategories)
            );
        }
        return $results;
    }

}