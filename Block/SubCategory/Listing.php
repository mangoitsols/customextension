<?php
/**
 * Mangoit Solutions Software.
 *
 * @category  Mangoit
 * @package   Mangoit_SubCategoryListing
 * @author    Mangoit
 * @copyright Copyright (c) Mangoit Solutions Private Limited (https://www.mangoitsolutions.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\SubCategoryListing\Block\SubCategory;

use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryRepository;

/**
 * Class Listing
 *
 * @package Mangoit\SubCategoryListing\Block\SubCategory
 */
class Listing extends \Magento\Framework\View\Element\Template implements BlockInterface
{
    protected $categoryRepository;
    protected $categoryCollectionFactory;
    protected $storeManager;
    protected $filesystem;
    protected $imageFactory;
    protected $productCollectionFactory;
    protected $productImageHelper;
    protected $subCategoryListingHelper;
    protected $categoryModel;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\Category  $categoryRepository
     * @param \Magento\Framework\Filesystem $filesystem  $filesystem
     * @param \Magento\Framework\Image\AdapterFactory  $imageFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
     * @param \Magento\Catalog\Helper\Image  $productImageHelper
     * @param \Mangoit\SubCategoryListing\Helper\Data  $helperData
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollectionFactory,
        CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\Category $category,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Helper\Image $productImageHelper,
        \Mangoit\SubCategoryListing\Helper\Data $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryModel = $category;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productImageHelper = $productImageHelper;
        $this->subCategoryListingHelper = $helperData;
        parent::__construct($context);
    }
    /**
     * Get module is enable/disable
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->subCategoryListingHelper->getModuleStatus();
    }
    /**
     *  Get the category collection based on the ids
     *
     * @return array
     */
    public function getCategoryCollection()
    {
        $category_ids = explode(",", $this->getCategoryIds());
        $condition = ['in' => array_values($category_ids) ];
        $collection = $this->categoryCollectionFactory->create()
        ->addAttributeToFilter('entity_id', $condition)
        ->addAttributeToSelect(['name', 'is_active', 'parent_id', 'image',
            'category_latitude', 'category_longitude', 'background_image', 'category_display_description'])
        ->addAttributeToFilter('is_active', 1)
        ->setStoreId($this->storeManager->getStore()->getId());
        return $collection;
    }
    /**
     * Returns category image.
     *
     * @param int $categoryId
     *
     * @return array
     */
    public function getCategoryImage($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        $categoryImage['url'] = $category->getImageUrl();
        $categoryImage['image'] = $category->getImage();
        $categoryImage['category_latitude'] = $category->getCategoryLatitude();
        $categoryImage['category_longitude'] = $category->getCategoryLongitude();
        $categoryImage['category_url'] = $category->getUrl();
        return $categoryImage;
    }
    /**
     * Returns resized image.
     *
     * @param image object $image
     * @param null  $width
     * @param null  $height
     *
     * @return image url
     */
    public function resize($image, $width = null, $height = null)
    {
        $directory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $absolutePath = $directory->getAbsolutePath('catalog/category/') . $image;
        $imageResized = $directory->getAbsolutePath('catalog/category/resized/' . $width . '/') . $image;
        $imageResize = $this->imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(false);
        $imageResize->resize($width, $height);
        //destination folder
        $destination = $imageResized;
        //save image
        $imageResize->save($destination);
        $path = 'catalog/category/resized/' . $width . '/' . $image;
        $resizedURL = $this->storeManager->getStore()
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$path;
        return $resizedURL;
    }
    /**
     *  Get the resizeProductImage based on the product, image id, width, height
     *
     * @param product object $product
     * @param image id $imageId
     * @param image width $width
     * @param null image height $height
     *
     * @return array
     */
    public function resizeProductImage($product, $imageId, $width, $height = null)
    {
        $resizedImage = $this->productImageHelper->init($product, $imageId)
        ->constrainOnly(true)->keepAspectRatio(true)
        ->keepTransparency(true)->keepFrame(false)
        ->resize($width, $height);
        return $resizedImage;
    }
    /**
     *  Get the category collection based on the ids
     *
     * @param int $categoryId
     * @param string $limit
     *
     * @return array
     */
    public function getCategoriesCollection($categoryId, $limit = '')
    {
        $collection = $this->categoryModel->getCollection()
        ->addAttributeToFilter('entity_id', $categoryId)
        ->addAttributeToFilter('is_active', 1)
        ->setPageSize($limit);
        return $collection;
    }
    /**
     *  Get the parent category name
     *
     * @param int $categoryId
     *
     * @return array
     */
    public function getParentCategory($categoryId)
    {
        $categoryModel = $this->categoryModel->load($categoryId);
        return $categoryModel;
    }
}
