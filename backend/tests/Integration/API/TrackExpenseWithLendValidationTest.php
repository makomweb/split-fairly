<?php

namespace App\Tests\Integration\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TrackExpenseWithLendValidationTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private User $user1;
    private User $user2;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        $container = $this->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        \assert($entityManager instanceof EntityManagerInterface);
        \assert($passwordHasher instanceof UserPasswordHasherInterface);

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

        // Clean up before each test
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Event')->execute();

        // Create test users
        $this->user1 = User::create('user1@example.com', ['ROLE_USER']);
        $plainPassword1 = 'password123';
        $hashedPassword1 = $this->passwordHasher->hashPassword($this->user1, $plainPassword1);
        $this->user1->setPassword($hashedPassword1);

        $this->user2 = User::create('user2@example.com', ['ROLE_USER']);
        $plainPassword2 = 'password123';
        $hashedPassword2 = $this->passwordHasher->hashPassword($this->user2, $plainPassword2);
        $this->user2->setPassword($hashedPassword2);

        $this->entityManager->persist($this->user1);
        $this->entityManager->persist($this->user2);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->createQuery('DELETE FROM App\Entity\Event')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();

        static::ensureKernelShutdown();
    }

    public function test_track_lend_expense_to_valid_user(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // Login as user1
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'user1@example.com',
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Track a Lend expense to user2
        $client->request('POST', '/api/track', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'price' => ['value' => 50.00, 'currency' => 'EUR'],
            'what' => 'cash',
            'type' => 'Lend',
            'location' => 'user2@example.com',
        ]));

        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];

        self::assertArrayHasKey('price', $response);
        self::assertArrayHasKey('what', $response);
        self::assertArrayHasKey('type', $response);
        self::assertArrayHasKey('location', $response);
        self::assertEquals('user2@example.com', $response['location']);
    }

    public function test_track_lend_expense_to_self_fails(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // Login as user1
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'user1@example.com',
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Try to track a Lend expense to self
        $client->request('POST', '/api/track', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'price' => ['value' => 50.00, 'currency' => 'EUR'],
            'what' => 'cash',
            'type' => 'Lend',
            'location' => 'user1@example.com',
        ]));

        self::assertResponseStatusCodeSame(400);
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];

        self::assertArrayHasKey('error', $response);
        self::assertStringContainsString('Cannot lend money to yourself', $response['error'] ?? '');
    }

    public function test_track_lend_expense_to_nonexistent_user_fails(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // Login as user1
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'user1@example.com',
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Try to track a Lend expense to non-existent user
        $client->request('POST', '/api/track', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'price' => ['value' => 50.00, 'currency' => 'EUR'],
            'what' => 'cash',
            'type' => 'Lend',
            'location' => 'nonexistent@example.com',
        ]));

        self::assertResponseStatusCodeSame(400);
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];

        self::assertArrayHasKey('error', $response);
        self::assertStringContainsString('not found', $response['error'] ?? '');
    }

    public function test_track_regular_expense_no_validation(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // Login as user1
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'user1@example.com',
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Track a regular Groceries expense (location can be anything)
        $client->request('POST', '/api/track', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'price' => ['value' => 25.50, 'currency' => 'EUR'],
            'what' => 'Coffee',
            'type' => 'Groceries',
            'location' => 'Starbucks',
        ]));

        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];

        self::assertEquals('Starbucks', $response['location'] ?? null);
    }
}
