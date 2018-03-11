<?php
namespace App\Twig;

use App\Entity\Product;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\RequestStack;

class HVFGridViewExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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
        //$request = $this->requestStack->getCurrentRequest();

        echo $environment->render('grid_view/table.html.twig', ['models' => $models]);
    }
}