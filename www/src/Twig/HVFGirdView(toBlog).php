<?php
namespace App\Twig;

use App\Entity\Product;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\Common\Annotations\AnnotationReader;

class HVFGirdViewto extends AbstractExtension
{
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
        $reader = new AnnotationReader();
        echo "<pre>";
        var_dump($models[0]->id);
        die();
        $reflClass = new \ReflectionClass(get_class($models[0]));
        $props = $reflClass->getProperties();
        foreach ($props as $prop) {
            $annotations = $reader->getPropertyAnnotation($prop, 'App\Annotations\HVFGridView');
            $columns[] = [
                'label' => $annotations->label ?? $prop->getName(),
                'name' => $prop->getName(),
                'value' => $reflClass->hasMethod('get' . $prop->getName())
            ];
            //$columns[$prop->getName()] = $reader->getPropertyAnnotation($prop, 'App\Annotations\HVFGridView');
        }
        echo "<pre>";
        var_dump($columns);
        die();
        echo $environment->render('grid_view/table.html.twig', ['models' => $models, 'columns' => $columns]);
    }

}