<?php
use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use PHPHtmlParser\Dom;

require __DIR__.'/../vendor/autoload.php';

$content = file_get_contents('https://catalog.onliner.by/memcards/samsung/mbmc64ga');
function get_inner_html( $node ) {
    $innerHTML= '';
    $children = $node->childNodes;
    foreach ($children as $child) {
        $innerHTML .= $child->ownerDocument->saveXML( $child );
    }

    return $innerHTML;
}
$doc = new DOMDocument();
$doc->loadHtml($content);
$tags = $doc->getElementsByTagName('a');
$i = 0;
foreach ($tags as $tag) {
    echo ++$i;
}
echo "<br>";
echo $i;
die();
//$dom = new Dom;
//$dom->loadStr($content, []);
//$html = $dom->find('.offers-description__details')[0];
//echo "<pre>";
//var_dump($html->outerHtml);
//die();
