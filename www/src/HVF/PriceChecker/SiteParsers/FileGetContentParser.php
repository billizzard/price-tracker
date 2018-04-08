<?php
namespace App\HVF\PriceChecker\SiteParsers;

class FileGetContentParser implements SiteParser
{
    public function getContent(string $url): string
    {
        $content = @file_get_contents($url);
        if (!$content) {
            // logging: нет результата для сайта
        }
        return $content;
    }
}