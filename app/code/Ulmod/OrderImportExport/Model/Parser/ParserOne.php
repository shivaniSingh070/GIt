<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Model\Parser;

use Ulmod\OrderImportExport\Model\Parser\ParserInterface;

class ParserOne implements ParserInterface
{
    /**
     * @var string
     */
    private $parentParseTagname;

    /**
     * @param string $parentParseTagname
     */
    public function __construct(
        $parentParseTagname
    ) {
        $this->parentParseTagname = $parentParseTagname;
    }

    /**
     * @param string $value
     * @return array
     */
    public function parse($value)
    {
        $result = [];

        $value = $this->stripUnwantedChars($value);
        
        $items = $this->getAllWrappedByTagname(
            $this->parentParseTagname,
            $value
        );

        $i = 0;
        foreach ($items as &$item) {
            $parts = explode(PHP_EOL, $item);

            foreach ($parts as &$part) {
                $tagname = $this->getTagname($part);
                if ($this->isArray($part)) {
                    $string = $this->removeTagname(
                        $tagname,
                        $part
                    );
                    $array = $this->toArray($string);
                    $result[$i][$tagname][] = $array;
                } else {
                    $result[$i][$tagname] = $this->removeTagname(
                        $tagname,
                        $part
                    );
                }
            }

            $i++;
        }

        return $result;
    }

    /**
     * @param string $tagname
     * @param string $value
     * @return array
     */
    private function getAllWrappedByTagname($tagname, $value)
    {
        preg_match_all('/\[\[' . $tagname . '\]\](.*)\[\[\/'
            . $tagname . '\]\]/misU', $value, $items);
        if (is_array($items)) {
            if (isset($items[1])) {
                if (is_array($items[1])) {
                    foreach ($items[1] as &$item) {
                        $item = trim($item, PHP_EOL);
                    }

                    return $items[1];
                }
            }
        }

        return [];
    }

    /**
     * @param string $value
     * @return string
     */
    private function stripUnwantedChars($value)
    {
        return trim(str_replace(
            ["\t", "\r", '  '],
            '',
            $value
        ));
    }

    /**
     * @param string $value
     * @return array
     */
    private function getTagnames($value)
    {
        preg_match_all(
            '/\[\[(.*)\]\]/U',
            $value,
            $matches
        );
        if (isset($matches[1])) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @param string $tagname
     * @param string $value
     * @return string
     */
    private function removeTagname($tagname, $value)
    {
        return preg_replace(
            '/^\[\[' . $tagname . '\]\]/U',
            '',
            trim($value),
            1
        );
    }

    /**
     * @param string $value
     * @return null|string
     */
    private function getTagname($value)
    {
        preg_match(
            '/^\[\[(.*)\]\]/U',
            $value,
            $matches
        );
        if (isset($matches[1])) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param string $value
     * @return array
     */
    private function getValues($value)
    {
        preg_match_all(
            '/\]\](.*)(\[\[|$)/U',
            trim($value),
            $matches
        );
        if (isset($matches[1])) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @param string $value
     * @return int
     */
    private function isArray($value)
    {
        return preg_match(
            '/^\[\[[^\/].*\]\]\[\[[^\/].*\]\]/U',
            $value
        );
    }

    /**
     * @param string $value
     * @return array
     */
    private function toArray($value)
    {
        $keys = $this->getTagnames($value);
        $values = $this->getValues($value);

        return array_combine($keys, $values);
    }
}
