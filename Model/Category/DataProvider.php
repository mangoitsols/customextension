<?php
namespace Mangoit\CategoryWidget\Model\Category;
  
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
  
    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'background_image'; // background image field
         
        return $fields;
    }
}
