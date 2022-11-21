<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Filtering;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\Filter\FieldFilterInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Source\Data\DataStructureProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;

class FilteringAction implements ActionInterface
{
    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var DataStructureProvider
     */
    private $dataStructureProvider;

    /**
     * @var SourceDataStructureInterface
     */
    private $dataStructure;

    /**
     * @var FieldFilterInterface[][]|null
     */
    private $fieldFiltersRegistry = null;

    public function __construct(
        FilterProvider $filterProvider,
        DataStructureProvider $dataStructureProvider
    ) {
        $this->filterProvider = $filterProvider;
        $this->dataStructureProvider = $dataStructureProvider;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        if (empty($this->fieldFiltersRegistry)) {
            return;
        }

        $importData = $importProcess->getData();
        $entityCode = $importProcess->getEntityConfig()->getEntityCode();

        $rowNumber = 1;
        $skippedRowNumbers = [];
        if ($importProcess->getBatchNumber() > 1) {
            $batchSize = $importProcess->getProfileConfig()->getOverflowBatchSize()
                ?:
                $importProcess->getProfileConfig()->getBatchSize();
            $step = $importProcess->getBatchNumber() - 1;
            $rowNumber += $batchSize * $step;
        }
        foreach ($importData as $key => &$row) {
            if (!$this->isNeedToSkip(
                $row,
                $this->fieldFiltersRegistry[$entityCode] ?? [],
                $this->dataStructure
            )) {
                unset($importData[$key]);
                $skippedRowNumbers[] = $rowNumber;
            }
            $rowNumber++;
        }
        $importProcess->setData(array_values($importData));
        $importProcess->addSkippedRowNumbers($skippedRowNumbers);
        $importProcess->setHasNonEmptyBatch(!empty($importProcess->getData()));

        if (!$importProcess->isHasNextBatch()) {
            $importProcess->addInfoMessage((string)__('The data is being filtered.'));
        }
    }

    public function initialize(ImportProcessInterface $importProcess): void
    {
        $entitiesConfig = $importProcess->getProfileConfig()->getEntitiesConfig();

        $this->fieldFiltersRegistry = $this->filterProvider->getFieldFilters(
            $entitiesConfig
        );

        $this->dataStructure = $this->dataStructureProvider->getDataStructure(
            $importProcess->getEntityConfig(),
            $importProcess->getProfileConfig()
        );
    }

    private function isNeedToSkip(
        array &$row,
        array $filters,
        SourceDataStructureInterface $dataStructure
    ): bool {
        if (!empty($filters)) {
            foreach ($dataStructure->getFields() as $field) {
                $fieldName = $dataStructure->getFieldName($field)
                    ? $dataStructure->getFieldName($field)
                    : $field;
                if (isset($filters[$fieldName])) {
                    /** @var FieldFilterInterface $filter */
                    foreach ($filters[$fieldName] as $filter) {
                        if (!$filter->apply($row, $fieldName)) {
                            return false;
                        }
                    }
                }
            }
        }

        $subEntityStructures = $dataStructure->getSubEntityStructures();
        foreach ($subEntityStructures as $subEntityStructure) {
            $entityCode = $subEntityStructure->getEntityCode();
            if (!isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode])) {
                continue;
            }

            $isSubEntityRowRemoved = false;
            foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode] as $key => &$subEntityRow) {
                if (!$this->isNeedToSkip(
                    $subEntityRow,
                    $this->fieldFiltersRegistry[$entityCode] ?? [],
                    $subEntityStructure
                )) {
                    unset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode][$key]);
                    $isSubEntityRowRemoved = true;
                }
            }

            if ($isSubEntityRowRemoved) {
                if (empty($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode])) {
                    return false;
                }

                $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode] = array_values(
                    $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode]
                );
            }
        }

        return true;
    }
}
