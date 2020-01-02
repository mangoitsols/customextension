<?php
namespace Mangoit\SubCategoryListing\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function getModuleStatus()
    {
        $module_enable = 0;
        if ($this->scopeConfig->getValue('subcategory_listing/general/enable', ScopeInterface::SCOPE_STORE)) {
            $module_enable = 1;
        }
        return $module_enable;
    }
}
