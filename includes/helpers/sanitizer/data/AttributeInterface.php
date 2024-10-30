<?php
// phpcs:ignoreFile
namespace enshrined\svgSanitize\data;

/**
 * Class AttributeInterface
 *
 * @package enshrined\svgSanitize\data
 */
if (!interface_exists('enshrined\svgSanitize\data\AttributeInterface')) {
interface AttributeInterface
{

    /**
     * Returns an array of attributes
     *
     * @return array
     */
    public static function getAttributes();
}
}