<?php
declare(strict_types=1);
namespace App;

class Application
{
    private $fileHandler;
    private $processFile;
    private $logger;

    public function __construct(
        FileHandler $fileHandler,
        ProcessFile $processFile,
        Logger $logger
    ) {
        // all Notices & Warnings into Errors
        \set_error_handler(Utils::class.'::strictErrorHandler');

        $this->fileHandler = $fileHandler;
        $this->processFile = $processFile;
        $this->logger = $logger;
    }

    /**
     * @param callable $output Callable to print the output.
     * Takes one parameter - the row array returned by ProcessFile::processFileRow()
     */
    public function run(callable $output)
    {
        $this->fileHandler->check();
        $filePointer = $this->fileHandler->openFile();

        $rowNum = 0;
        while (($rawData = \fgetcsv($filePointer)) !== false) {
            // ignore empty row
            if (empty($rawData[0])) {
                continue;
            }

            $rowNum++;
            $invalidRow = '';
            $row = $this->processFile->processFileRow($rawData, $rowNum, $invalidRow);
            // report an invalid row
            if ($invalidRow) {
                $this->logger->log('Invalid row: '.$invalidRow);
                continue;
            }
            $output($row);
        }
    }

    public static function newInstance(string $fileName, array $config): Application
    {
        $fileHandler = new FileHandler($fileName);
        $logger = new Logger($config['log_file']);
        $processFile = new ProcessFile();
        return new Application($fileHandler, $processFile, $logger);
    }
}
