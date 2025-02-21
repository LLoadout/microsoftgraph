<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;

/**
 * Excel Class for Microsoft Graph API Integration
 * 
 * This class provides functionality to interact with Excel files stored in OneDrive
 * through Microsoft Graph API. It allows for reading, writing, and manipulating Excel
 * workbooks and their contents.
 */
class Excel
{
    use Connect,
        Authenticate;

    /** @var string The ID of the current Excel file being accessed */
    private string $fileId = '';

    /** @var string The session ID for the current Excel workbook session */
    private string $excelSession = '';

    /**
     * Load an Excel file from OneDrive using its file object
     *
     * @param string $file JSON string containing file information with an 'id' property
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException When API request fails
     * @throws \Microsoft\Graph\Exception\GraphException When Graph API encounters an error
     */
    public function loadFile($file)
    {
        $this->loadFileById(json_decode($file)->id);
    }

    /**
     * Load an Excel file directly using its OneDrive file ID
     *
     * @param string $fileId The OneDrive file ID of the Excel workbook
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
     * Set values in a specified range of cells
     *
     * Updates the values of cells in the specified range. The values are automatically
     * formatted as a 2D array where each value is wrapped in an array.
     *
     * @param string $cellRange Excel range notation (e.g., "A1:B5")
     * @param array $values Array of values to set in the range
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException When API request fails
     * @throws \Microsoft\Graph\Exception\GraphException When Graph API encounters an error
     */
    public function setCellValues($cellRange, array $values): void
    {
        $values = array_values(collect($values)->map(function ($value, $key) {
            return [$value];
        })->toArray());

        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/{00000000-0001-0000-0000-000000000000}/range(address=\''.$cellRange.'\')';
        $this->patch($url, ['values' => $values], headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Retrieve values from a specified range of cells
     *
     * Gets the values and formatting information from the specified cell range.
     *
     * @param string $cellRange Excel range notation (e.g., "A1:B5")
     * @return array Contains cell values and additional range properties
     * @throws \GuzzleHttp\Exception\GuzzleException When API request fails
     * @throws \Microsoft\Graph\Exception\GraphException When Graph API encounters an error
     */
    public function getCellValues($cellRange)
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/{00000000-0001-0000-0000-000000000000}/range(address=\''.$cellRange.'\')';

        return $this->get($url, headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Recalculate all formulas in the workbook
     *
     * Triggers a recalculation of all formulas in the workbook. This is useful
     * after making changes that affect formula results.
     *
     * @return array Response from the calculation request
     */
    public function recalculate()
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/application/calculate';

        return $this->get($url);
    }

    /**
     * Clear contents or formatting from a range of cells
     *
     * Removes specified aspects (values, formatting, or both) from the target range.
     *
     * @param string $cellRange Excel range notation (e.g., "A1:B5")
     * @param string $applyTo What to clear: "All" (default), "Formats", or "Contents"
     * @param string $worksheet ID of the worksheet (defaults to first worksheet)
     * @return void
     */
    public function clearRange($cellRange, string $applyTo = 'All', $worksheet = '00000000-0001-0000-0000-000000000000'): void
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/'.$worksheet.'/range(address=\''.$cellRange.'\')/clear';
        $this->post($url, ['applyTo' => $applyTo]);
    }

    /**
     * Retrieve all worksheets in the workbook
     *
     * Returns detailed information about all worksheets including their names,
     * visibility, and position in the workbook.
     *
     * @return array List of worksheet objects with their properties
     * @throws \GuzzleHttp\Exception\GuzzleException When API request fails
     * @throws \Microsoft\Graph\Exception\GraphException When Graph API encounters an error
     */
    public function getWorksheets()
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets';
        return $this->get($url, headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Create a new worksheet in the workbook
     *
     * Adds a new worksheet with the specified name to the end of the workbook.
     *
     * @param string $name Name for the new worksheet
     * @return array Details of the created worksheet
     * @throws \GuzzleHttp\Exception\GuzzleException When API request fails
     * @throws \Microsoft\Graph\Exception\GraphException When Graph API encounters an error
     */
    public function addWorksheet(string $name)
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/add';
        return $this->post($url, ['name' => $name], headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Remove a worksheet from the workbook
     *
     * Permanently deletes the specified worksheet and all its contents.
     *
     * @param string $worksheetId ID of the worksheet to remove
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException When API request fails
     * @throws \Microsoft\Graph\Exception\GraphException When Graph API encounters an error
     */
    public function deleteWorksheet(string $worksheetId): void
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/'.$worksheetId;
        $this->delete($url, headers: ['workbook-session-id' => $this->excelSession]);
    }

    /**
     * Initialize a new Excel session
     *
     * Creates a persistent session for interacting with the Excel file. This session
     * maintains the state of the workbook during multiple operations.
     *
     * @param string $fileId The OneDrive file ID of the Excel workbook
     * @return string Session ID for the created session
     * @throws \GuzzleHttp\Exception\GuzzleException When API request fails
     * @throws \Microsoft\Graph\Exception\GraphException When Graph API encounters an error
     */
    private function createSession($fileId): string
    {
        return $this->connect()->createRequest('POST', '/me/drive/items/'.$fileId.'/workbook/createSession')->execute()->getBody()['id'];
    }
}
