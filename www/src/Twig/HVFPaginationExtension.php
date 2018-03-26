<?php
namespace App\Twig;

use App\Service\HVFUrlBuilder;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\RequestStack;

class HVFPaginationExtension extends AbstractExtension
{
    private $requestStack;
    private $urlBuilder;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        //$this->urlBuilder = new HVFUrlBuilder($_SERVER['REQUEST_URI']);
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'pagination',
                array($this, 'paginationView'),
                array('needs_environment' => true)
            ),
        );
    }

    public function paginationView(\Twig_Environment $environment, $data)
    {
        if ($data['total']) {
            $request = $this->requestStack->getCurrentRequest();
            $data['current'] = $request->get('page') ? $request->get('page') : 1;

            if ($data['total'] > 7) {
                if ($data['current'] > ($data['total'] - 3)) {
                    $data['start'] = $data['total'] - 6;
                } else {
                    $data['start'] = $data['current'] > 3 ? $data['current'] - 3 : 1;
                }


                if ($data['current'] < 4) {
                    $data['end'] = 7;
                } else {
                    $data['end'] = (($data['current'] + 3) > $data['total']) ? $data['total'] : $data['current'] + 3;
                }
            } else {
                $data['end'] = $data['total'];
                $data['start'] = 1;
            }
            $data['prevClass'] = $data['current'] == 1 ? 'disabled' : '';
            $data['nextClass'] = $data['current'] == $data['total'] ? 'disabled' : '';
            //$data['url'] = $this->urlBuilder->removeParam('page')->addParam('page', '')->getUrl();
        }

        echo $environment->render('grid_view/pagination.html.twig', ['data' => $data]);
    }
}