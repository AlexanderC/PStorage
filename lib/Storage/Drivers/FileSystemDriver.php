<?php
/**
 * @author AlexanderC
 */

namespace PStorage\Storage\Drivers;


class FileSystemDriver implements IDriver
{
    const READ_LOCK = 0x001;
    const WRITE_LOCK = 0x002;

    /**
     * @var string
     */
    protected $root;

    /**
     * @param string $root
     * @throws \BadMethodCallException
     */
    public function __construct($root)
    {
        if(empty($root) || !is_dir($root) || !is_writable($root)) {
            throw new \BadMethodCallException("Root folder should be provided and write able");
        }

        $this->root = realpath($root);
    }

    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param string $file
     * @param string $string
     * @return bool
     * @throws \RuntimeException
     */
    public function write($file, $string)
    {
        $result = false;
        $file = $this->getAbsoluteFilePath($file);

        $handler = fopen($file, 'w');

        if(!$handler) {
            throw new \RuntimeException("Unable to open {$file} file for writing");
        }

        while(!$this->lock($handler, self::WRITE_LOCK));

        if(fwrite($handler, $string) === strlen($string)) {
            $result = true;
        } else {
            while(!ftruncate($handler, 0));
        }

        while(!$this->unlock($handler));
        fclose($handler);

        return $result;
    }

    /**
     * @param string $file
     * @return string
     * @throws \RuntimeException
     */
    public function read($file)
    {
        $file = $this->getAbsoluteFilePath($file);

        $handler = fopen($file, 'rb');

        if(!$handler) {
            throw new \RuntimeException("Unable to open {$file} file for reading");
        }

        while(!$this->lock($handler, self::READ_LOCK));

        $string = fread($handler, filesize($file));

        while(!$this->unlock($handler));
        fclose($handler);

        return $string;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function exists($file)
    {
        $file = $this->getAbsoluteFilePath($file);

        $result = is_file($file) && is_writeable($file) && is_readable($file);

        // collect garbages...
        if($result && @filesize($result) === 0) {
            @unlink($file);
            $result = false;
        }

        return $result;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        $file = $this->getAbsoluteFilePath($file);
        return @unlink($file);
    }

    /**
     * @param string $directory
     * @return bool
     */
    public function isDirectory($directory)
    {
        $directory = $this->getAbsoluteFilePath($directory);

        return is_dir($directory) && is_writeable($directory);
    }

    /**
     * @param string $directory
     * @param int $rights
     * @return bool
     */
    public function createDirectory($directory, $rights = 0777)
    {
        $directory = $this->getAbsoluteFilePath($directory);

        return @mkdir($directory, $rights);
    }

    /**
     * @param resource $handler
     * @param int $type
     * @return bool
     * @throws \BadMethodCallException
     */
    protected function lock($handler, $type)
    {
        if(!in_array($type, [self::READ_LOCK, self::WRITE_LOCK])) {
            throw new \BadMethodCallException("Lock type should be both READ_LOCK or WRITE_LOCK");
        }

        return flock($handler, $type === self::READ_LOCK ? LOCK_SH : LOCK_EX);
    }

    /**
     * @param resource $handler
     * @return bool
     */
    protected function unlock($handler)
    {
        return flock($handler, LOCK_UN);
    }

    /**
     * @param string $pattern
     * @return array
     *
     * TODO: replace glob() with something quicker
     */
    public function & glob($pattern)
    {
        $pattern = $this->getAbsoluteFilePath($pattern);
        $results = glob($pattern);

        foreach($results as & $file) {
            $file = preg_replace("/^" . preg_quote($this->root, "/") . "\//ui", "", $file);
        }

        return $results;
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getAbsoluteFilePath($file)
    {
        return sprintf("%s/%s", $this->root, ltrim(str_replace("\\", "/", $file), "/"));
    }
}