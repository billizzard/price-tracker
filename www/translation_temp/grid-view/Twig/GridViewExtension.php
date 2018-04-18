<?php


namespace Billizzard\GridView\Twig;


use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;

class GridViewExtension extends \Twig_Extension
{

    public function __construct(RequestStack $requestStack)
    {
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'grid_view',
                array($this, 'gridView'),
                array('needs_environment' => true)
            ),
        );
    }

    public function gridView(\Twig_Environment $environment, $models)
    {
        //$loader = new \Twig_Loader_Filesystem(__DIR__ . '/Templates/');
        /** @var \Twig_Loader_Filesystem $loader */
        $loader = $environment->getLoader();
        $loader->addPath(__DIR__ . '/templates/');
        echo $environment->render('bz-grid-view.html.twig', ['models' => $models]);
    }
}