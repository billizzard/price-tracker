<?php
namespace App\Service;

use App\Entity\Product;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\RequestStack;

class HVFGridView
{
    private $refClass;
    private $requestStack;
    private $conf;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getGridData(array $models)
    {
        $result = [];
        if (count($models)) {
            $this->getConf($models);
            $this->getColumns($result);
            $this->getData($models, $result);
            $this->getSort($result);
        }
        
        return $result;
    }

    private function getConf($models)
    {
        $reader = new AnnotationReader();
        $this->refClass = new \ReflectionClass(get_class($models[0]));
        $props = $this->refClass->getProperties();
        foreach ($props as $prop) {
            $annotations = $reader->getPropertyAnnotation($prop, 'App\Annotations\HVFGrid');
            if ($annotations) {
                $this->conf[mb_strtolower($prop->getName())] = [
                    'sort' => (bool)$annotations->sort,
                ];
            }
        }
    }

    private function getColumns(&$result)
    {
        $result['columns'] = [];
        $props = $this->refClass->getProperties();

        if ($props) {
            foreach ($props as $prop) {
                $name = mb_strtolower($prop->getName());
                $result['columns'][$name] = [
                    'label' => $prop->getName(),
                    'sortClass' => (isset($this->conf[$name]['sort']) && $this->conf[$name]['sort'] == true) ? 'sorting' : '',
                ];
            }
        }
    }

    private function getData($models, &$result)
    {
        foreach ($models as $model) {
            foreach ($result['columns'] as $key => $column) {
                $result['data'][$model->getId()][$key] = [
                    'value' => $this->refClass->hasMethod('get' . $key) ? $model->{'get' . $key}() : '',
                ];
            }
        }
    }

    private function getSort(&$result)
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($sort = $request->get('sort')) {
            $sortDirection = 'asc';

            if ($sort[0] === '-') {
                $sort = mb_substr($sort, 1);
                $sortDirection = 'desc';
            }

            if (isset($this->conf[$sort]) && $this->conf[$sort]) {
                if (isset($result['columns'][$sort])) {
                    $result['columns'][$sort]['sortClass'] = 'sorting_' . $sortDirection;
                }
            }
        }
    }
}