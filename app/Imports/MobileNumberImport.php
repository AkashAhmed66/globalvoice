<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class MobileNumberImport implements ToArray
{
    public function array(array $rows)
    {
        return $rows; // Return the rows of the Excel sheet as an array
    }
}