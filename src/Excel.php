<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;

class Excel
{
    use Connect,
        Authenticate;

    private string $fileId = '';

    private string $excelSession = '';

    /**
     * Load an excel file from a onedrive file
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function loadFile($file)
    {
        $this->loadFileById(json_decode($file)->id);
    }

    public function loadFileById($fileId)
    {
        $this->fileId = $fileId;
        if (blank($this->excelSession)) {
            $this->excelSession = $this->createSession($fileId);
        }
    }

    /**
     * Set the values of a cell or range of cells
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function setCellValues($cellRang, array $values)
    {
        $values = array_values(collect($values)->map(function ($value, $key) {
            return [$value];
        })->toArray());

        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/{00000000-0001-0000-0000-000000000000}/range(address=\''.$cellRang.'\')';
        $this->connect()->createRequest('PATCH', $url)->addHeaders(['workbook-session-id' => $this->excelSession])->attachBody(['values' => $values])->execute();
    }

    /**
     * Set the values of a cell or range of cells
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getCellValues($cellRange)
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/{00000000-0001-0000-0000-000000000000}/range(address=\''.$cellRange.'\')';

        return $this->connect()->createRequest('GET', $url)->addHeaders(['workbook-session-id' => $this->excelSession])->execute()->getBody();
    }

    /**
     * Calculate the workbook
     *
     * @return void
     */
    public function recalculate()
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/application/calculate';

        return $this->connect()->createRequest('GET', $url)->execute()->getBody();
    }

    /**
     * Create a session for the excel file
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    private function createSession($fileId): string
    {
        return $this->connect()->createRequest('POST', '/me/drive/items/'.$fileId.'/workbook/createSession')->execute()->getBody()['id'];
    }
}
