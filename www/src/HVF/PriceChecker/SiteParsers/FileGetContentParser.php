<?php
namespace App\HVF\PriceChecker\SiteParsers;

class FileGetContentParser implements SiteParser
{
    public function getContent(string $url): string
    {
        return file_get_contents($url);
    }
}