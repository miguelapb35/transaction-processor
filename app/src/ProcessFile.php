<?php
declare(strict_types=1);
namespace App;

class ProcessFile
{
    public function processFileRow(array $rawData, int $rowNum, &$invalidRow = ''):? array
    {
        $rowError = function ($rowNum, $i) use (&$invalidRow) {
            $invalidRow = 'row #' . $rowNum . ' field #' . ($i+1);
        };

        $numFields = count($rawData);
        $row = [];
        for ($i = 0; $i < $numFields; $i++) {
            if ($i === 0) {
                $row['date'] = new \DateTime($rawData[$i]);
                if (empty($row['date'])) {
                    $rowError($rowNum, $i);
                    return null;
                }
            } elseif ($i === 1) {
                $row['acc_id'] = (int) $rawData[$i];
                if (empty($row['acc_id'])) {
                    $rowError($rowNum, $i);
                    return null;
                }
            } elseif ($i === 2) {
                $row['acc_type'] = $this->replaceAccountType(trim(strtolower($rawData[$i])));
                if (!in_array($row['acc_type'], ['natural', 'legal'])) {
                    $rowError($rowNum, $i);
                    return null;
                }
            } elseif ($i === 3) {
                $row['trans_type'] = $this->replaceTransactionName(trim(strtolower($rawData[$i])));
                if (!in_array($row['trans_type'], [TransactionManager::CASH_IN, TransactionManager::CASH_OUT])) {
                    $rowError($rowNum, $i);
                    return null;
                }
            } elseif ($i === 4) {
                $row['amount'] = (float) $rawData[$i];
                if (empty($row['amount'])) {
                    $rowError($rowNum, $i);
                    return null;
                }
            } elseif ($i === 5) {
                $row['cur'] = trim(strtoupper($rawData[$i]));
                if (!in_array($row['cur'], ['EUR', 'USD', 'JPY'])) {
                    $rowError($rowNum, $i);
                    return null;
                }
            }
        }

        return $row;
    }

    /**
     * Replace the strings in the input file with constants to prevent from future changes
     * @param string $rowName
     * @return string
     */
    private function replaceTransactionName(string $rowName): string
    {
        if ($rowName === 'cash_in') {
            return TransactionManager::CASH_IN;
        } elseif ($rowName === 'cash_out') {
            return TransactionManager::CASH_OUT;
        }
        return $rowName;
    }

    private function replaceAccountType(string $rowName): string
    {
        if ($rowName === 'natural') {
            return Account::PRIVATE;
        } elseif ($rowName === 'natural') {
            return Account::BUSINESS;
        }
        return $rowName;
    }
}
