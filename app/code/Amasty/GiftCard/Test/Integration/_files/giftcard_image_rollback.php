<?php

use Amasty\GiftCard\Model\Image\ImageBakingProcessor;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Filesystem $filesystem */
$filesystem = $objectManager->create(Filesystem::class);

$mediaWriter = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
$mediaWriter->delete(
    FileUpload::AMGIFTCARD_IMAGE_MEDIA_PATH . DIRECTORY_SEPARATOR
    . FileUpload::ADMIN_UPLOAD_PATH . DIRECTORY_SEPARATOR . 'test_giftcard_image.jpg'
);
$mediaWriter->delete(ImageBakingProcessor::FONT_FILE_ARIAL);
