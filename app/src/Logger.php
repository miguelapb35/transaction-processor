<?php
declare(strict_types=1);
namespace App;

class Logger
{
    private $filePointer;

    public function __construct(string $fileName)
    {
        $this->filePointer = \fopen($fileName, 'a');
    }

    public function log(string $message, bool $isError = true): void
    {
        $type = $this->setType($isError);
        \fprintf($this->filePointer, "%s %s: %s".PHP_EOL, \date('Y-m-d H:i:s'), $type, $message);
    }

    private function setType(bool $isError = true)
    {
        return $isError ? 'ERROR' : ' INFO';
    }

    public function __destruct()
    {
        \fclose($this->filePointer);
    }
}
