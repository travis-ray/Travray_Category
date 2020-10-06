<?php

namespace Travray\Category\Block;
use Magento\Framework\View\Element\Template;
class Category extends \Magento\Framework\View\Element\Template {

    protected $_categoryCollectionFactory;
    protected $_productRepository;
    protected $_registry;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->_registry = $registry;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getCategories()
    {
        $return = '';
        $product = $this->getCurrentProduct();
        $categoryIds = $product->getCategoryIds();
        $categories = $this->getCategoryCollection()
            ->addAttributeToFilter('entity_id', $categoryIds);
        $template = $this->_scopeConfig->getValue('travray_category/general/format', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($categories as $category) {
            $return .= str_replace('{{category}}', $category->getName(), $template);
        }
        return $return;
    }
}