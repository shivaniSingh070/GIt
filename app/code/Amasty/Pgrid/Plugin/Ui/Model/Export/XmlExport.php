<?php

declare(strict_types=1);

namespace Amasty\Pgrid\Plugin\Ui\Model\Export;

use Magento\Framework\Convert\Excel;
use Magento\Ui\Model\Export\ConvertToXml;

class XmlExport extends AbstractExport
{
    /**
     * @var string
     */
    protected $exportType = 'xml';

    /**
     * @param ConvertToXml $subject
     * @param \Closure $proceed
     * @return array|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetXmlFile(
        ConvertToXml $subject,
        \Closure $proceed
    ) {
        return $this->checkNamespace() ? $this->getXmlFile() : $proceed();
    }

    /**
     * Returns Excel XML file
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getXmlFile()
    {
        $component = $this->filter->getComponent();
        $file = 'export/'. $component->getName() . $this->random->getRandomString(16) . '.xml';
        $productItems = $this->getComponentProductItems($component);
        $fieldMapping = $this->getSortedColumnFieldMapping($component);
        $this->prepareFieldMappingOptions($component, $fieldMapping);
        $searchResultIterator = $this->iteratorFactory->create(['items' => $productItems]);
        /** @var Excel $excel */
        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback'=> [$this, 'getRowXmlData'],
        ]);
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $excel->setDataHeader($this->getHeaders($component, $fieldMapping));
        $excel->write($stream, $component->getName() . '.xml');
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
