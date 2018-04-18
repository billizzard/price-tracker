<?php


namespace Billizzard\GridView\Twig;


use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;

class GridView2Extension extends \Twig_Extension
{

    public function __construct(RequestStack $requestStack)
    {
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'grid_view2',
                array($this, 'gridView2'),
                array('needs_environment' => true)
            ),
        );
    }

    public function gridView2(\Twig_Environment $environment, $models)
    {
        //$loader = new \Twig_Loader_Filesystem(__DIR__ . '/Templates/');
        /** @var \Twig_Loader_Filesystem $loader */
        $loader = $environment->getLoader();
        $loader->addPath(__DIR__ . '/templates/');
        echo $environment->render('bz-grid-view2.html.twig', ['models' => $models]);
    }
}