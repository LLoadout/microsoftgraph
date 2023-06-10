<?php

namespace LLoadout\Microsoftgraph;

use Microsoft\Graph\Graph;

class Excel
{
    use \LLoadout\Microsoftgraph\Traits\Authenticate;

    private string $fileId;

    private Graph  $graph;

    private mixed  $excelSession;

    public function loadFile($file)
    {
        $graph = (new Graph())->setAccessToken($this->getAccessToken());
        $this->fileId = json_decode($file)->id;

        $request = $graph->createRequest('post', '/me/drive/items/'.$this->fileId.'/workbook/createSession');
        $session = $request->execute()->getBody();
        $this->excelSession = $session['id'];
        $this->graph = $graph;
    }

    public function setCellValues($cell, $values)
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/{00000000-0001-0000-0000-000000000000}/range(address=\''.$cell.'\')';
        $this->graph->createRequest('patch', $url)->addHeaders(['workbook-session-id' => $this->excelSession])->attachBody(['values' => $values])->execute();
    }


    public function getCell($cell)
    {
        $url = '/me/drive/items/'.$this->fileId.'/workbook/worksheets/{00000000-0001-0000-0000-000000000000}/range(address=\''.$cell.'\')';

        return $this->graph->createRequest('get', $url)->addHeaders(['workbook-session-id' => $this->excelSession])->execute()->getBody();
    }

    public function recalculate()
    {
        $url = config('services.office.api_url').'/me/drive/items/01CPKWSS4XOQSWSLYELBEKGUFVUI7JQVG7/workbook/application/calculate';
        $this->doPost('post', $url, []);
    }
}
