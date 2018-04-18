<?php
namespace Billizzard\GridView\Helper;

class UrlBuilder
{
    private $originalUrl;
    private $resultUrl;
    private $dataUrl;

    public function __construct(string $url)
    {
        $this->originalUrl = $url;
        $this->resetUrl();
    }

    public function addParam($name, $value)
    {
        $this->dataUrl['params'][$name] = $value;
        return $this;
    }

    public function removeParam($name)
    {
        if (isset($this->dataUrl['params'][$name])) unset($this->dataUrl['params'][$name]);
        return $this;
    }

    public function getUrl()
    {
        return $this->dataUrl['path'] . '?' . http_build_query($this->dataUrl['params']);
    }

    public function resetUrl()
    {
        $this->resultUrl = $this->originalUrl;
        $this->dataUrl = parse_url($this->originalUrl);
        if (!isset($this->dataUrl['query'])) $this->dataUrl['query'] = '';
        parse_str($this->dataUrl['query'], $this->dataUrl['params']);
        return $this;
    }
}