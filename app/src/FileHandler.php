<?php
declare(strict_types=1);
namespace App;

class FileHandler
{
    private $fileName;

    public function __construct(string $fileName = null)
    {
        if ($fileName) {
            $this->fileName = $fileName;
        }
    }

    public function check()
    {
        $this->validateFileName();
        $this->checkFileReadable();
    }

    public function validateFileName(): void
    {
        // the file name should not be empty
        // and should only contains alphanumeric characters, numbers, -, _, . and space
        if (empty($this->fileName)) {
            throw new \InvalidArgumentException("The file name parameter is empty");
        } elseif (!preg_match('/^[\w\-. \/\\\\]+$/', $this->fileName)) {
            throw new \InvalidArgumentException("The file name you provided is invalid. Allowed symbols are
            all the alphanumeric characters plus, - _ . / \ and space.");
        }
    }

    public function checkFileReadable()
    {
        if (!is_readable($this->fileName)) {
            throw new \InvalidArgumentException("File with name {$this->fileName} does not exist");
        }
    }

    /**
     * @return resource
     */
    public function openFile()
    {
        $filePointer = @\fopen($this->fileName, 'r');
        if (!$filePointer) {
            throw new \RuntimeException("Failed opening the file with name {$this->fileName}");
        }
        return $filePointer;
    }
}
