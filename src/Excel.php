<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;

/**
 * Microsoft Graph API Excel Integration
 *
 * Provides functionality to interact with Excel files stored in OneDrive through the Microsoft Graph API.
 * Supports operations like:
 * - Reading and writing cell values
 * - Managing worksheets
 * - Clearing ranges
 * - Recalculating formulas
 * - Managing Excel sessions
 */
class Excel
{
    use Connect,
        Authenticate;

    /** @var string The OneDrive file ID of the Excel workbook being accessed */
    private string $fileId = '';

    /** @var string The session ID for maintaining state across API calls */
    private string $excelSession = '';

    /** @var string The ID of the currently active worksheet */
    private string $worksheetId = '';

    /**
     * Load an Excel file using a file object from OneDrive
     *
     * @param string $file JSON string containing OneDrive file info with 'id' property
     * @return void
     */
    public function loadFile($file)
    {
        $this->loadFileById(json_decode($file)->id);
    }

    /**
     * Load an Excel file using its OneDrive ID
     *
     * @param string $fileId OneDrive file ID
     * @return void
     */
    public function loadFileById($fileId)
    {
        $this->fileId = $fileId;
        if (blank($this->excelSession)) {
            $this->excelSession = $this->createSession($fileId);
        }
    }

    /**
     * Set the active worksheet
     *
     * @param string $worksheetId ID of worksheet to activate
     * @return void
     */
    public function setWorksheet($worksheetId): void
    {
        $this->worksheetId  = $worksheetId;
    }

    /**
     * Write values to an Excel range
     *
     * Values are automatically formatted as a 2D array with each value wrapped in an array.
     *
     * @param string $cellRange Range in Excel notation (e.g. "A1:B5")
     * @param array $values Values to write to the range
     * @return void
     */
    public function setCellValues($cellRange, array $values): void
    {
        $values = array_values(collect($values)->map(function ($value, $key) {
            return [$value];
        })->toArray());

        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/'.$this->getActiveWorksheet().'/range(address=\''.$cellRange.'\')';
        $this->patch($url, ['values' => $values], headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Read values from an Excel range
     *
     * @param string $cellRange Range in Excel notation (e.g. "A1:B5")
     * @return array Cell values and range properties
     */
    public function getCellValues($cellRange)
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/'.$this->getActiveWorksheet().'/range(address=\''.$cellRange.'\')';

        return $this->get($url, headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Recalculate all formulas in the workbook
     *
     * @return array API response from calculation request
     */
    public function recalculate()
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/application/calculate';

        return $this->get($url);
    }

    /**
     * Clear contents and/or formatting from a range
     *
     * @param string $cellRange Range to clear in Excel notation
     * @param string $applyTo What to clear: "All", "Formats", or "Contents"
     * @return void
     */
    public function clearRange($cellRange, string $applyTo = 'All'): void
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/'.$this->getActiveWorksheet().'/range(address=\''.$cellRange.'\')/clear';
        $this->post($url, ['applyTo' => $applyTo]);
    }

    /**
     * Get all worksheets in the workbook
     *
     * @return array List of worksheet objects with properties like name, visibility, position
     */
    public function getWorksheets()
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets';
        return $this->get($url, headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Create a new worksheet
     *
     * @param string $name Name for the new worksheet
     * @return array Created worksheet details
     */
    public function addWorksheet(string $name)
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/add';
        return $this->post($url, ['name' => $name], headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Delete a worksheet
     *
     * @param string $worksheetId ID of worksheet to delete
     * @return void
     */
    public function deleteWorksheet(string $worksheetId): void
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/'.$worksheetId;
        $this->delete($url, headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Create a new Excel session
     *
     * Sessions maintain workbook state across multiple API operations.
     *
     * @param string $fileId OneDrive file ID
     * @return string Session ID
     */
    private function createSession($fileId): string
    {
        return $this->connect()->createRequest('POST', '/me/drive/items/'.$fileId.'/workbook/createSession')->execute()->getBody()['id'];
    }

    /**
     * Get the active worksheet ID
     *
     * If no worksheet is set, defaults to the first worksheet.
     *
     * @return string Worksheet ID
     */
    public function getActiveWorksheet()
    {
        //if no worksheet is set, get the first worksheet
        if (blank($this->worksheetId)) {
            $worksheets = $this->getWorksheets();
            $this->worksheetId = $worksheets[0]['id'];
        }
        return $this->worksheetId;
    }

}
