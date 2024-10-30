<?php
// phpcs:ignoreFile
namespace enshrined\svgSanitize\data;

/**
 * Interface TagInterface
 *
 * @package enshrined\svgSanitize\tags
 */
if (!interface_exists('enshrined\svgSanitize\data\TagInterface')) {
interface TagInterface
{

    /**
     * Returns an array of tags
     *
     * @return array
     */
    public static function getTags();

}
}