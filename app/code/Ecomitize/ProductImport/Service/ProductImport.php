<?php

namespace Ecomitize\ProductImport\Service;

use Ecomitize\ProductImport\Helper\Config;
use Ecomitize\ProductImport\Service\ImportImageService;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 *
 */
class ProductImport
{
    /**
     * @var UrlPersistInterface
     */
    private UrlPersistInterface $urlPersist;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    private CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator;
    /**
     * @var \Magento\Framework\Xml\Parser
     */
    protected $parser;
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Ecomitize\ProductImport\Service\ImportImageService
     */
    protected $importImageService;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;
    /**
     * @var \Magento\Eav\Setup\AddOptionToAttribute
     */
    protected $addOptionToAttribute;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\OptionManagement
     */
    protected $optionManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $ruStore;
    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $uaStore;
    /**
     * @var array
     */
    protected $attributeOptions = [];
    /**
     * @var array
     */
    protected $allCategories = [];

    /**
     *
     */
    const OUT_OF_STOCK = [
        'Немає в наявності',
        'Нет в наличии'
    ];

    /**
     *
     */
    const UA = 'ukrainian';
    /**
     *
     */
    const RU = 'russian';


    /**
     * @param \Magento\Framework\Xml\Parser $parser
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Setup\AddOptionToAttribute $addOptionToAttribute
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Eav\Model\Entity\Attribute\OptionManagement $optionManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param ProductFactory $productFactory
     * @param \Ecomitize\ProductImport\Service\ImportImageService $importImageService
     * @param ProductRepositoryInterface $productRepository
     * @param Config $config
     * @param UrlPersistInterface $urlPersist
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Setup\AddOptionToAttribute $addOptionToAttribute,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Eav\Model\Entity\Attribute\OptionManagement $optionManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        ProductFactory $productFactory,
        ImportImageService $importImageService,
        ProductRepositoryInterface $productRepository,
        Config $config,
        UrlPersistInterface $urlPersist,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
    ) {
        $this->parser = $parser;
        $this->productFactory = $productFactory;
        $this->importImageService = $importImageService;
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        $this->addOptionToAttribute = $addOptionToAttribute;
        $this->storeRepository= $storeRepository;
        $this->optionManagement = $optionManagement;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->config = $config;
        $this->urlPersist = $urlPersist;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->uaStore = $this->storeRepository->get(static::UA);
        $this->ruStore = $this->storeRepository->get(static::RU);
        $this->initCategories();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initCategories()
    {
        $collection = $this->categoryCollectionFactory->create()->addAttributeToSelect('name');

        $this->fillAllCategories($collection, static::UA);

        $collection = $this->categoryCollectionFactory->create()->setStoreId($this->ruStore->getId())->addAttributeToSelect('name');

        $this->fillAllCategories($collection, static::RU);
    }

    /**
     * @param $collection
     * @param $storeCode
     * @return void
     */
    protected function fillAllCategories($collection, $storeCode)
    {
        foreach ($collection as $category) {
            $this->allCategories[$storeCode][$category->getName()] = $category;
        }
    }

    /**
     * @param $code
     * @return array|string
     */
    public function readXmlData($filePath)
    {
        return $this->parser->load($filePath)->xmlToArray();
    }

    /**
     * @return true
     */
    public function execute()
    {
        $ua = $this->readXmlData($this->config->getUaUrl());
        $ru = $this->readXmlData($this->config->getRuUrl());
        $this->setManufacturer($ua, $ru);
        $this->setCategories($ua, $ru);
        $this->initCategories();

        foreach ($ua['books']['Product'] as $arrayProduct) {
            $product = $this->initProduct($arrayProduct['Ean']);
            if ($product) {
                $this->productImport($product, $arrayProduct, static::UA);
            }
        }

        foreach ($ru['books']['Product'] as $arrayProduct) {
            $product = $this->initProduct($arrayProduct['Ean'], $this->ruStore->getId());
            if ($product) {
                $this->productImport($product, $arrayProduct, static::RU);
            }
        }

        return true;
    }

    /**
     * @param $ean
     * @param $storeId
     * @return bool|\Magento\Catalog\Model\AbstractModel|null
     */
    protected function initProduct($ean, $storeId = 0)
    {
        if (!$ean) {
            return null;
        }

        $product = $this->productFactory->create()->setStoreId($storeId)->loadByAttribute('sku', trim($ean));

        if ($product && $product->getId()) {
            return $product;
        }

        return $this->productFactory->create()
            ->setSku($ean)
            ->setAttributeSetId(4)
            ->setWeight(10)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setTaxClassId(0)
            ->setTypeId('simple');
    }

    /**
     * @param $product
     * @param $arrayProduct
     * @param $storeCode
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     */
    protected function productImport($product, $arrayProduct, $storeCode)
    {
        $product->setManufacturer($this->getManufacturerId($arrayProduct['Manufacturer'], $storeCode));
        $product->setCategoryIds($this->getCategoryIds($arrayProduct['Category'], $storeCode));
        $product->setName($arrayProduct['Product_title']);
        $product->setDescription($arrayProduct['Description']);
        $product->setCharacteristics($arrayProduct['Characteristics']);
        $product->setDeliveryKit($arrayProduct['Delivery_kit']);
        $product->setPrice($this->getProductPrice($arrayProduct));
        $product->setStockData($this->getStockData($arrayProduct));

        try {
            $product = $this->productRepository->save($product);
        } catch (\Exception $exception) {
            $product->save();
        }

        $this->importImageService->execute($product, $arrayProduct['Image_URL'], $exclude = false, $imageType = ['image', 'small_image', 'thumbnail', 'swatch_image']);

        return $product;
    }

    /**
     * @param $ua
     * @param $ru
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setCategories($ua, $ru)
    {
        $uaCategories = [];
        $parentId = $this->uaStore->getRootCategoryId();
        $parentCategory = $this->categoryFactory->create()->load($parentId);

        foreach ($ua['books']['Product'] as $value) {
            if (isset($value['Category']) && $value['Category'] && !in_array($value['Category'], $uaCategories)) {
                $uaCategories[] = $value['Category'];
                $category = null;

                if (isset($this->allCategories[static::UA][$value['Category']])) {
                    $category = $this->allCategories[static::UA][$value['Category']];
                }

                if (!$category) {
                    try {
                        $category = $this->categoryFactory->create();
                        $category->setPath($parentCategory->getPath())
                            ->setParentId($parentId)
                            ->setName($value['Category'])
                            ->setIsActive(true);
                        $category->save();
                    } catch (\Exception $exception) {
                        continue;
                    }
                }

                if ($category->getId()) {
                    $ruCategory = $this->categoryFactory->create()->setStoreId($this->ruStore->getId())->load($category->getId());
                    if (isset($value['Ean']) && $value['Ean']) {
                        $ruCategoryName = $this->getValueBy($ru, $value['Ean'], 'Ean', 'Category');
                        if ($ruCategoryName != $ruCategory->getName()) {
                            $ruCategory->setName($ruCategoryName);
                            $ruCategory->save();

                            $this->urlPersist->deleteByData(
                                [
                                    UrlRewrite::ENTITY_ID => $ruCategory->getId(),
                                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                                    UrlRewrite::REDIRECT_TYPE => 0,
                                    UrlRewrite::STORE_ID => $this->ruStore->getId()
                                ]
                            );

                            $newUrls = $this->categoryUrlRewriteGenerator->generate($ruCategory);

                            $newUrls = $this->filterEmptyRequestPaths($newUrls);
                            $this->urlPersist->replace($newUrls);

                            try {
                                $newUrls = $this->filterEmptyRequestPaths($newUrls);
                                $this->urlPersist->replace($newUrls);
                            } catch (\Exception $e) {
                                var_dump($e->getMessage());
                                continue;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $newUrls
     * @return array
     */
    private function filterEmptyRequestPaths(array $newUrls): array
    {
        $result = [];

        foreach ($newUrls as $key => $url) {
            $requestPath = $url->getRequestPath();

            if (!empty($requestPath)) {
                $result[$key] = $url;
            }
        }

        return $result;
    }

    /**
     * @param $categoryName
     * @param $storeCode
     * @return array
     */
    protected function getCategoryIds($categoryName, $storeCode)
    {
        $category = null;

        if (isset($this->allCategories[$storeCode][$categoryName])) {
            $category = $this->allCategories[$storeCode][$categoryName];
        }

        if ($category && $category->getId()) {
            return [$category->getId()];
        }
        return [];
    }

    /**
     * @param $ua
     * @param $ru
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function setManufacturer($ua, $ru)
    {
        $uaOptions = [];
        foreach ($ua['books']['Product'] as $key => $value) {
            if (isset($value['Manufacturer']) && $value['Manufacturer'] && !in_array($value['Manufacturer'], $uaOptions)) {
                $uaOptions[] = $value['Manufacturer'];
            }
        }

        $attribute = $this->eavConfig->getAttribute('catalog_product', 'manufacturer');

        $this->addOptionToAttribute->execute([
            'values'       => $uaOptions,
            'attribute_id' => $attribute->getAttributeId()
        ]);

        foreach ($attribute->setStoreId(0)->getOptions() as $option) {
            if (!empty(trim($option->getLabel()))) {
                $uaLabel = $this->getValueBy($ua, $option->getLabel(), 'Manufacturer', 'Manufacturer');
                $storeLabels = [];

                if ($uaLabel) {
                    $storeLabels[$this->uaStore->getStoreId()] = $this->getLabelObject($this->uaStore->getStoreId(), $uaLabel);
                }

                $ean = $this->getValueBy($ua, $option->getLabel(), 'Manufacturer', 'Ean');
                if ($ean) {
                    $manufacturer = $this->getValueBy($ru, $ean, 'Ean', 'Manufacturer');
                    if ($manufacturer) {
                        $storeLabels[$this->ruStore->getStoreId()] = $this->getLabelObject($this->ruStore->getStoreId(), $manufacturer);
                    }
                }

                if (!empty($storeLabels)) {
                    $option->setStoreLabels($storeLabels);

                    $this->optionManagement->update(4, 'manufacturer', $option->getValue(), $option);
                }
            }
        }
    }

    /**
     * @param $items
     * @param $attribute
     * @param $attributeName
     * @param $returnAttributeName
     * @return false|mixed
     */
    protected function getValueBy($items, $attribute, $attributeName, $returnAttributeName)
    {
        foreach ($items['books']['Product'] as $value) {
            if (isset($value[$attributeName]) && $value[$attributeName] && $value[$attributeName] == $attribute &&
                isset($value[$returnAttributeName]) && $value[$returnAttributeName]
            ) {
                return $value[$returnAttributeName];
            }
        }

        return false;
    }

    /**
     * @param $storeId
     * @param $labelString
     * @return \Magento\Framework\DataObject
     */
    protected function getLabelObject($storeId, $labelString)
    {
        $label = new \Magento\Framework\DataObject();
        $label->setStoreId($storeId);
        $label->setLabel($labelString);

        return $label;
    }

    /**
     * @param $manufacturer
     * @param $storeCode
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getManufacturerId($manufacturer, $storeCode)
    {
        if (empty($this->attributeOptions[$storeCode])) {
            $store = $this->storeRepository->get($storeCode);
            $attribute = $this->eavConfig->getAttribute('catalog_product', 'manufacturer');
            $this->attributeOptions[$storeCode] = $attribute->setStoreId($store->getId())->getOptions();
        }

        foreach ($this->attributeOptions[$storeCode] as $option) {
            if ($option->getLabel() == $manufacturer) {
                return $option->getValue();
            }
        }

        return false;

    }

    /**
     * @param $arrayProduct
     * @return array|float|string|string[]|null
     */
    protected function getProductPrice($arrayProduct)
    {
        if (isset($arrayProduct['Retail_price']) && $arrayProduct['Retail_price']) {
            return preg_replace("/[^0-9\.]/", '', $arrayProduct['Retail_price']);
        }

        return 0.00;
    }

    /**
     * @param $arrayProduct
     * @return int[]
     */
    protected function getStockData($arrayProduct)
    {
        $stockData = [
            'use_config_manage_stock' => 0,
            'manage_stock' => 1,
            'is_in_stock' => 1,
            'qty' => 100
        ];

        if ($arrayProduct['Item_address'] && in_array($arrayProduct['Item_address'], self::OUT_OF_STOCK)) {
            $stockData['is_in_stock'] = 0;
            $stockData['qty'] = 0;
        }

        return $stockData;
    }
}
