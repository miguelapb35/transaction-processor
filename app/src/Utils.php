<?php

namespace App;

class Utils
{
    /**
     * Convert PHP Notices & Warnings into exceptions
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @return bool
     */
    public static function strictErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(\error_reporting() & $errno)) {
            // This error code is not included in error_reporting, so let it fall
            // through to the standard PHP error handler
            return false;
        }

        switch ($errno) {
            case E_WARNING:
            case E_NOTICE:
                throw new \RuntimeException('Error occured: '.$errstr.PHP_EOL.'In file: '.
                    $errfile.':'.$errline.PHP_EOL);
        }

        /* Execute PHP internal error handler */
        return false;
    }

    /**
     * Rounds decimals up eg: 0.02001 becomes 0.03
     * courtesy: https://stackoverflow.com/a/12278063/2239549
     * @param $val
     * @param $precision
     * @return float|int
     */
    public static function ceil(float $val, int $precision): float
    {
        $mult = pow(10, $precision);
        return ceil($val * $mult) / $mult;
    }

    /**
     * Format the output according to requirement
     * @param float $amount
     * @param array $row
     * @param array $config
     * @return string
     */
    public static function formatOutput(float $amount, array $row, array $config): string
    {
        $decimals = $config['currency']['precision'][$row['cur']];
        return number_format($amount, $decimals, '.', '');
    }
}
