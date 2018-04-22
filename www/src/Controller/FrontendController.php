<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 *
 * See http://knpbundles.com/keyword/admin
 * *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */

class FrontendController extends Controller
{
    public function render(string $view, array $parameters = array(), Response $response = null): Response
    {
        $parameters['languages'] = [
            [
                'code' => 'ru',
                'title' => 'Русский',
                'url' => '/ru' . substr($_SERVER['REQUEST_URI'], 3)
            ],
            [
                'code' => 'en',
                'title' => 'English',
                'url' => '/en' . substr($_SERVER['REQUEST_URI'], 3)
            ],
        ];
        return parent::render($view, $parameters, $response);
    }
}
