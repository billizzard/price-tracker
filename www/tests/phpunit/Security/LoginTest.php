<?php
namespace App\Tests\phpunit\Security;

use App\Entity\Host;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Watcher;
use App\HVF\PriceChecker\PriceParsers\OnlinerCatalogParser;
use App\HVF\PriceChecker\PriceParsers\PriceParser;
use App\Kernel;
use App\Tests\phpunit\BaseTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class LoginTest extends BaseTestCase
{
    private $url = '/en/login/';

    /**
     * Корректный логин
     */
    public function testCorrectLogin()
    {
        self::createTestUser();
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $form['_username'] = 'test@test.com';
        $form['_password'] = 'qq';
        $form['_csrf_token'] = $csrfToken;

        $client->request('POST', $this->url, $form, [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(true, $response->authenticated);
        $this->assertContains('/en/profile/', $response->url);
    }

    /**
     * Некорректный логин
     */
    public function testIncorrectLogin()
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $form['_username'] = 'qq@qq.qq';
        $form['_password'] = 'qq';
        $form['_csrf_token'] = $csrfToken;

        $client->request('POST', $this->url, $form, [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $response->authenticated);
        $this->assertNotEmpty($response->error);
    }
}