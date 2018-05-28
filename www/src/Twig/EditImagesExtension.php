<?php
namespace App\Twig;

use App\Entity\File;
use App\HVF\Helper\UrlBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class EditImagesExtension extends \Twig_Extension
{
    private $requestStack;
    private $urlBuilder;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->urlBuilder = new UrlBuilder($_SERVER['REQUEST_URI']);
        }
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'editImages',
                array($this, 'editImagesFunction'),
                array('needs_environment' => true)
            ),
        );
    }

    public function editImagesFunction(\Twig_Environment $environment, array $files, array $options = [])
    {
        $images = [];

        if (is_array($files)) {
            /** @var File $file */
            foreach ($files as $file) {
                if ($file) {
                    $images[] = [
                        'id' => $file->getId(),
                        'src' => $file->getSrc(),
                    ];
                }
            }
        }

        $data = [
            'images' => $images,
            'options' => $options,
        ];

        echo $environment->render('extension/editImages.html.twig', $data);
    }
}