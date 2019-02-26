<?php
/**
 * Created by PhpStorm.
 * User: Sylvain
 * Date: 26.02.2019
 * Time: 17:55
 */

namespace Ergo\Services;

final class FileUtility
{
    public function __construct() {}

    /**
     * Secure way to search a filename for multiple extensions in a specific directory and return the filename and the extension
     * @param string $filename
     * @param array $extensions
     * @param string $absolutePath
     * @return array|null
     */
    public function searchFile(string $filename, array $extensions, string $absolutePath) : ?array {
        foreach (glob($absolutePath . '*{' . implode(',', $extensions) . '}', GLOB_BRACE) as $file) {
            $urlSegments = explode('/', rtrim($file, '/'));
            foreach ($extensions as $extension) {
                if ($filename . '.' . $extension === end($urlSegments)) {
                    return ['filename' => $filename, 'extension' => $extension];
                }
            }
        }
        return null;
    }

    /**
     * Scan a directory and return an array of filename who match with given extensions
     * @param array $extensions
     * @param string $absolutePath
     * @return array
     */
    public function scanDirectory(array $extensions, string $absolutePath) : array
    {
        $files = [];
        foreach (glob($absolutePath . '*{' . implode(',', $extensions) . '}', GLOB_BRACE) as $file) {
            $urlSegments = explode('/', rtrim($file, '/'));
            $files[] = end($urlSegments);
        }
        return $files;
    }
}
