<?php

declare(strict_types=1);

namespace Amasty\Pgrid\Plugin\Ui\Model\Export;

class CsvExport extends AbstractExport
{
    /**
     * @var string
     */
    protected $exportType = 'csv';

    /**
     * @param \Magento\Ui\Model\Export\ConvertToCsv $subject
     * @param \Closure $proceed
     * @return array|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetCsvFile(
        \Magento\Ui\Model\Export\ConvertToCsv $subject,
        \Closure $proceed
    ) {
        return $this->checkNamespace() ? $this->getCsvFile() : $proceed();
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();
        $file = 'export/'. $component->getName() . $this->random->getRandomString(16) . '.csv';
        $productItems = $this->getComponentProductItems($component);
        $fieldMapping = $this->getSortedColumnFieldMapping($component);
        $this->prepareFieldMappingOptions($component, $fieldMapping);
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->getHeaders($component, $fieldMapping));

        foreach ($productItems as $item) {
            $this->metadataProvider->convertDate($item, $component->getName());
            $stream->writeCsv($this->getRowData($item, $fieldMapping));
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
