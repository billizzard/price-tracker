<?php
namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\RequestStack;

class HVFGridView
{
    private $refClass;
    private $request;
    private $conf;
    private $qb;
    private $urlBuilder;

    public function __construct(Request $request, QueryBuilder $qb, $conf = [])
    {
        $this->request = $request;
        $this->qb = $qb;
        $this->conf['pagination'] = [
            'curPage' => (int)$this->request->get('page', 1),
            'perPage' => (int)$conf['perPage'] ? (int)$conf['perPage'] : 10
        ];
        $this->urlBuilder = new HVFUrlBuilder($_SERVER['REQUEST_URI']);
    }

    public function getGridData()
    {
        $result = [];
        $models = $this->getModels($result);

        if (count($models)) {
            $this->getConf($models);
            $this->getColumns($result);
            $this->getData($models, $result);
            $this->getSort($result);
        }
        
        return $result;
    }

    /**
     * Читает конфигурацию из аннотаций
     * @param $models
     */
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

    /**
     * Создает атрибуты колонок из аннотаций
     * @param $result
     */
    private function getColumns(&$result)
    {
        $result['columns'] = [];
        $props = $this->refClass->getProperties();

        if ($props) {
            foreach ($props as $prop) {
                $name = mb_strtolower($prop->getName());
                $isSortable = (isset($this->conf[$name]['sort']) && $this->conf[$name]['sort'] == true) ? true : false;
                $result['columns'][$name] = [
                    'label' => $prop->getName(),
                    'sortClass' => $isSortable ? 'sorting' : '',
                    'sortUrl' => $isSortable ? $this->urlBuilder->addParam('sort', $name)->getUrl() : ''
                ];
            }
        }
    }

    /**
     * Наполняет данными для таблицы
     * @param $models
     * @param $result
     */
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

    /**
     * Формирует данные для сортировки
     * @param $result
     */
    private function getSort(&$result)
    {
        if ($sortColumn = $this->request->get('sort')) {
            $sortDirection = 'asc';

            if ($sortColumn[0] === '-') {
                $sortColumn = mb_substr($sortColumn, 1);
                $sortDirection = 'desc';
            }

            if (isset($this->conf[$sortColumn]['sort']) && $this->conf[$sortColumn]['sort']) {
                if (isset($result['columns'][$sortColumn])) {
                    $result['columns'][$sortColumn]['sortClass'] = 'sorting_' . $sortDirection;
                    $result['columns'][$sortColumn]['sortUrl'] = $this->urlBuilder->addParam(
                        'sort', $sortDirection === 'desc' ? $sortColumn : '-' . $sortColumn
                    )->getUrl();
                }
            }
        }
    }

    /**
     * Получает нужные модели в зависимости от страницы
     * @param $result
     * @return array
     */
    private function getModels(&$result): array
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($this->qb));
        $pager->setMaxPerPage($this->conf['pagination']['perPage']);
        $pager->setCurrentPage($this->conf['pagination']['curPage']);
        $result['pagination'] = $this->conf['pagination'];
        $currentResults = (array)$pager->getCurrentPageResults();
        $result['pagination']['total'] = $pager->getNbResults();
        $result['pagination']['totalPage'] = ceil($result['pagination']['total']/$result['pagination']['perPage']);
        $result['pagination']['url'] = $this->urlBuilder->resetUrl()->removeParam('page')->addParam('page', '')->getUrl();
        $result['pagination']['prevClass'] = $result['pagination']['curPage'] == 1 ? 'disabled' : '';
        $result['pagination']['nextClass'] = $result['pagination']['curPage'] == $result['pagination']['totalPage'] ? 'disabled' : '';
        $result['pagination']['startPage'] = $result['pagination']['curPage'] > 4 ? $result['pagination']['curPage'] - 3 : 1;
        $result['pagination']['endPage'] = (($result['pagination']['curPage'] + 3) > $result['pagination']['totalPage']) ? $result['pagination']['totalPage'] : ($result['pagination']['curPage'] + 3);

        return $currentResults;
    }

}