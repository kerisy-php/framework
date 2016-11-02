<?php
/**
 * use zan framework code
 *
 * User: Peter Wang
 * Date: 16/9/9
 * Time: 下午2:43
 */

namespace Trendi\Support;

use Trendi\Support\Exception\InvalidArgumentException;

class Dir
{
    
    const SCAN_CURRENT_DIR = 'current';
    const SCAN_BFS = 'bfs';
    const SCAN_DFS = 'dfs';

    /**
     *  search files
     *
     * @param $path
     * @param null $pattern
     * @param string $strategy
     * @return array
     * @throws InvalidArgumentException
     */
    public static function glob($path, $pattern = null, $strategy = self::SCAN_DFS)
    {
        if (!is_dir($path) || !$pattern) {
            throw new InvalidArgumentException('invalid $path or $pattern for Dir::glob');
        }

        $files = Dir::scan($path, $strategy);
        $result = [];
        foreach ($files as $file) {
            if (false === self::matchPattern($pattern, $file)) {
                continue;
            }
            $result[] = $file;
        }
        return $result;
    }

    /**
     * @param $path
     * @param string $strategy
     * @param bool $excludeDir
     * @return array
     * @throws InvalidArgumentException
     */
    public static function scan($path, $strategy = self::SCAN_CURRENT_DIR, $excludeDir = true)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException('invalid $path for Dir::scan');
        }

        switch ($strategy) {
            case self::SCAN_CURRENT_DIR:
                $files = self::scanCurrentDir($path, $excludeDir);
                break;
            case self::SCAN_BFS:
                $files = self::scanBfs($path, $excludeDir);;
                break;
            case self::SCAN_DFS:
                $files = self::scanDfs($path, $excludeDir);
                break;
            default:
                throw new InvalidArgumentException('invalid $strategy for Dir::glob');
        }

        return $files;
    }

    /**
     *  path init , add /
     * @param $path
     * @return string
     */
    public static function formatPath($path)
    {
        if ('/' == substr($path, -1)) {
            return $path;
        }

        return $path . '/';
    }

    /**
     * @param $pattern
     * @param $file
     * @return bool
     */
    public static function matchPattern($pattern, $file)
    {
        $replaceMap = [
            '*' => '.*',
            '.' => '\.',
            '+' => '.+',
            '/' => '\/',
        ];

        $pattern = str_replace(array_keys($replaceMap), array_values($replaceMap), $pattern);
        $pattern = '/' . $pattern . '/i';

        if (preg_match($pattern, $file)) {
            return true;
        }

        return false;
    }

    /**
     *  get basename
     * @param $pathes
     * @param string $suffix
     * @return array
     */
    public static function basename($pathes, $suffix = '')
    {
        if (!$pathes) return [];

        $ret = [];
        foreach ($pathes as $path) {
            $ret[] = basename($path, $suffix);
        }

        return $ret;
    }

    /**
     * scan
     *
     * @param $path
     * @param bool $excludeDir
     * @return array
     */
    private static function scanCurrentDir($path, $excludeDir = true)
    {
        $path = self::formatPath($path);
        $dh = opendir($path);
        if (!$dh) return [];

        $files = [];
        while (false !== ($file = readdir($dh))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $fileType = filetype($path . $file);
            if ('dir' == $fileType && false === $excludeDir) {
                $files[] = $path . $file . '/';
            }
            if ('file' == $fileType) {
                $files[] = $path . $file;
            }
        }
        closedir($dh);
        return $files;
    }

    /**
     *
     * @param $path
     * @param bool $excludeDir
     * @return array
     */
    private static function scanBfs($path, $excludeDir = true)
    {
        $files = [];
        $queue = new \SplQueue();
        $queue->enqueue($path);

        while (!$queue->isEmpty()) {
            $file = $queue->dequeue();
            $fileType = filetype($file);
            if ('dir' == $fileType) {
                $subFiles = self::scanCurrentDir($file, false);
                foreach ($subFiles as $subFile) {
                    $queue->enqueue($subFile);
                }
                if (false === $excludeDir && $file != $path) {
                    $files[] = $file;
                }
            }
            if ('file' == $fileType) {
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * @param $path
     * @param bool $excludeDir
     * @return array
     */
    private static function scanDfs($path, $excludeDir = true)
    {
        $files = [];
        $subFiles = self::scanCurrentDir($path, false);

        foreach ($subFiles as $subFile) {
            $fileType = filetype($subFile);
            if ('dir' == $fileType) {
                $innerFiles = self::scanDfs($subFile, $excludeDir);
                $files = Arr::join($files, $innerFiles);
                if (false === $excludeDir) {
                    $files[] = $subFile;
                }
            }
            if ('file' == $fileType) {
                $files[] = $subFile;
            }
        }
        return $files;
    }

    public static function getTempDir()
    {
        // @codeCoverageIgnoreStart
        if (function_exists('sys_get_temp_dir')) {
            $tmp = sys_get_temp_dir();
        } elseif (!empty($_SERVER['TMP'])) {
            $tmp = $_SERVER['TMP'];
        } elseif (!empty($_SERVER['TEMP'])) {
            $tmp = $_SERVER['TEMP'];
        } elseif (!empty($_SERVER['TMPDIR'])) {
            $tmp = $_SERVER['TMPDIR'];
        } else {
            $tmp = getcwd();
        }
        // @codeCoverageIgnoreEnd

        return $tmp;
    }
}