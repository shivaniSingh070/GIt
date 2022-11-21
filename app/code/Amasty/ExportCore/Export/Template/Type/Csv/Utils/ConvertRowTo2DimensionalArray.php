<?php

namespace Amasty\ExportCore\Export\Template\Type\Csv\Utils;

class ConvertRowTo2DimensionalArray
{
    public function convert(array $row, array $headerStructure, bool $duplicateParent = false)
    {
        $result = [];

        $this->fillRowResult($result, [$row], $headerStructure, 0, 0, 0, $duplicateParent);
        $resultOutput = [];
        foreach ($result as $resultRow) {
            end($resultRow);
            $outputRow = $resultRow + array_fill(0, key($resultRow) + 1, '');
            ksort($outputRow);

            $resultOutput[] = $outputRow;
        }

        return $resultOutput;
    }

    public function fillRowResult(
        &$result,
        $rows,
        $headerStructure,
        $lineCounter = 0,
        $level = 0,
        $offset = 0,
        $duplicateParent = false
    ) {
        $curOffset = $offset;
        foreach ($rows as $row) {
            $offset = $curOffset;
            $curLine = $nextLine = $lineCounter;
            foreach ($headerStructure as $field => $subentityStructure) {
                if (is_array($subentityStructure)) {
                    [$offset, $maxLine] = $this->fillRowResult(
                        $result,
                        !empty($row[$field]) ? $row[$field] : [[]],
                        $subentityStructure,
                        $lineCounter,
                        $level + 1,
                        $offset,
                        $duplicateParent
                    );
                    $nextLine = max($nextLine + 1, $maxLine) - 1;
                } else {
                    $result[$curLine][$offset] = $row[$field] ?? '';
                    $offset++;
                }
            }
            $lineCounter = $nextLine + 1;

            if ($duplicateParent && isset($result[$curLine - 1])) {
                foreach ($result[$curLine - 1] as $parentKey => $parentValue) {
                    if ($parentKey == $offset) {
                        break;
                    }
                    if (!isset($result[$curLine][$parentKey])) {
                        $result[$curLine][$parentKey] = $parentValue;
                    }
                }
                ksort($result[$curLine]);
            }
        }

        return [$offset, $lineCounter];
    }
}
