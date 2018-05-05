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


class RegistrationTest extends BaseTestCase
{
    private $url = '/en/registration/';

    /**
     * Проверка на слишком короткий пароль
     */
    public function testTooShortPassword()
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('registration_type_form');

        $form['registration']['email'] = 'qq@qq.qq';
        $form['registration']['plainPassword']['first'] = 'qq';
        $form['registration']['plainPassword']['second'] = 'qq';
        $form['registration']['_token'] = $csrfToken;

        $client->request('POST', $this->url, $form, [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $response->success);
        $this->assertContains('too short', $response->data->message);
    }

    /**
     * Проверка на то, что пароли не совпадают
     */
    public function testNotEqualsPassword()
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('registration_type_form');

        $form['registration']['email'] = 'qq@qq.qq';
        $form['registration']['plainPassword']['first'] = 'qqqqqq';
        $form['registration']['plainPassword']['second'] = 'aaaaaa';
        $form['registration']['_token'] = $csrfToken;

        $client->request('POST', $this->url, $form, [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $response->success);
        $this->assertContains('not match', $response->data->message);
    }

    /**
     * Проверка на некорректный email
     */
    public function testIncorrectEmail()
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('registration_type_form');

        $form['registration']['email'] = 'qqqq.qq';
        $form['registration']['plainPassword']['first'] = 'qqqqqq';
        $form['registration']['plainPassword']['second'] = 'qqqqqq';
        $form['registration']['_token'] = $csrfToken;

        $client->request('POST', $this->url, $form, [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $response->success);
        $this->assertContains('valid email', $response->data->message);
    }

    /**
     * Успешная регистрация
     */
    public function testCorrectRegistration()
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('registration_type_form');

        $form['registration']['email'] = 'qq@qq.qq';
        $form['registration']['plainPassword']['first'] = 'qqqqqq';
        $form['registration']['plainPassword']['second'] = 'qqqqqq';
        $form['registration']['_token'] = $csrfToken;

        $client->request('POST', $this->url, $form, [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(true, $response->success);
        $this->assertContains('/en/login/', $response->data->url);
    }

    /**
     * Успешная регистрация
     */
    public function testEmailExists()
    {
        self::createTestUser();
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('registration_type_form');

        $form['registration']['email'] = 'test@test.com';
        $form['registration']['plainPassword']['first'] = 'qqqqqq';
        $form['registration']['plainPassword']['second'] = 'qqqqqq';
        $form['registration']['_token'] = $csrfToken;

        $client->request('POST', $this->url, $form, [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(false, $response->success);
        $this->assertContains('already', $response->data->message);
    }
    
    

}