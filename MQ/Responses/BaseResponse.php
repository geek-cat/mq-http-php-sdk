<?php
declare(strict_types=1);
namespace MQ\Responses;

use MQ\Exception\MQException;

abstract class BaseResponse
{
    protected $succeed;
    protected $statusCode;
    // from header
    protected $requestId;

    abstract public function parseResponse($statusCode, $content);

    abstract public function parseErrorResponse($statusCode, $content, MQException|null $exception = NULL);

    public function isSucceed()
    {
        return $this->succeed;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    public function getRequestId()
    {
        return $this->requestId;
    }

    protected function loadXmlContent($content)
    {
        $xmlReader = new \XMLReader();
        if (is_object($content) && get_class($content) == 'GuzzleHttp\\Psr7\\Stream') {
            $content = $content->getContents();
        }
        $isXml = $xmlReader->XML($content);
        if ($isXml === FALSE) {
            throw new MQException($this->statusCode, $content);
        }
        try {
            while ($xmlReader->read()) {}
        } catch (\Exception $e) {
            throw new MQException($this->statusCode, $content);
        }
        $xmlReader->XML($content);
        return $xmlReader;
    }

    protected function loadAndValidateXmlContent($content, &$xmlReader)
    {
        $doc = new \DOMDocument();
        if(!$doc->loadXML($content)) {
            return false;
        }
        $xmlReader = $this->loadXmlContent($content);
        return true;
    }
}


