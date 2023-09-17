<?php

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const API_TOKEN = 'd38df2a992911aadd51891fb9b7f95f24bdf0f18731c38bfc7c314daa67ed4894e2b1edadc9214f15dfef3890a63ec69f6862a826c924559cfc4bc30';
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('info@santinisystems.com');
        $user->setPassword('Welcome01');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $apiToken = new ApiToken();
        $apiToken->setToken(self::API_TOKEN);
        $apiToken->setUser($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

    }

    public function testGetCollection(): void
    {
        $response = $this->client->request('GET', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);

        self::assertResponseIsSuccessful();

        self::assertResponseHeaderSame('Content-Type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/products?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:next' => '/api/products?page=2'
            ]
        ]);

        self::assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testPagination(): void
    {
        $response = $response = $this->client->request('GET', '/api/products?page=2', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);

        self::assertResponseIsSuccessful();

        self::assertResponseHeaderSame('Content-Type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/products?page=2',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:previous' => '/api/products?page=1',
                'hydra:next' => '/api/products?page=3'
            ]
        ]);

        self::assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testCreateProduct(): void
    {

        $this->client->request('POST', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN],
            'json' => [
                'mpn'          => '1234',
                'name'         => 'A Test Product',
                'description'  => 'A Test Description',
                'issueDate'    => '1985-07-31',
                'manufacturer' => '/api/manufacturers/1',
            ]
        ]);

        self::assertResponseStatusCodeSame(201);

        self::assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );

        self::assertJsonContains([
            'mpn'          => '1234',
            'name'         => 'A Test Product',
            'description'  => 'A Test Description',
            'issueDate'    => '1985-07-31T00:00:00+00:00'
        ]);
    }

    public function testUpdateProduct(): void
    {
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => self::API_TOKEN],
            'json' => ['description' => 'An updated description',
        ]]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id'         => '/api/products/1',
            'description' => 'An updated description',
        ]);
    }

    public function testCreateInvalidProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN],
            'json' => [
                'mpn'          => '1234',
                'name'         => 'A Test Product',
                'description'  => 'A Test Description',
                'issueDate'    => '1985-07-31',
                'manufacturer' => null,
            ]
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context'          => '/api/contexts/ConstraintViolationList',
            '@type'             => 'ConstraintViolationList',
            'hydra:title'       => 'An error occurred',
            'hydra:description' => 'manufacturer: This value should not be null.',
        ]);
    }

    public function testInvalidToken(): void
    {
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['x-api-token' => 'fake-token'],
            'json' => [
                'description' => 'An updated description',
            ]
        ]);

        self::assertResponseStatusCodeSame(401);
        self::assertJsonContains([
            'message'         => 'Invalid credentials.'
        ]);
    }
}