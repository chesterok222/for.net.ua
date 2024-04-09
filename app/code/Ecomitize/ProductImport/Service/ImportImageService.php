<?php

namespace Ecomitize\ProductImport\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class ImportImageService
 * assign images to products by image URL
 */
class ImportImageService
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param DirectoryList $directoryList
     * @param File $file
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        DirectoryList $directoryList,
        File $file,
        ProductRepositoryInterface $productRepository
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->productRepository = $productRepository;
    }

    /**
     * Main service executor
     *
     * @param Product $product
     * @param string $imageUrl
     * @param array $imageType
     * @param bool $visible
     *
     * @return bool
     */
    public function execute($product, $imageUrl, $visible = false, $imageType = [])
    {
        if (!$imageUrl) {
            return false;
        }

        if ($product->getImage() && baseName($product->getImage()) == baseName($imageUrl)) {
            return false;
        }

        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        /** create folder if it is not exists */
        $this->file->checkAndCreateFolder($tmpDir);
        /** @var string $newFileName */
        $newFileName = $tmpDir . baseName($imageUrl);
        /** read file from URL and copy it to the new destination */
        $result = $this->file->read($imageUrl, $newFileName);
        if ($result) {
            /** add saved file to the $product gallery */
            $product->addImageToMediaGallery($newFileName, $imageType, true, $visible);
            $product->save();
        }

        return $result;
    }

    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
    }
}
