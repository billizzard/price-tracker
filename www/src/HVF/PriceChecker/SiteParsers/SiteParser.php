<?php
namespace App\HVF\PriceChecker\SiteParsers;

interface SiteParser
{
    public function getContent(string $url);
}