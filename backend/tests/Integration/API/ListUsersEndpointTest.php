<?php

namespace App\Tests\Integration\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ListUsersEndpointTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private UserPasswordHasherInterface $passwordHasher;

    private User $user1;

    private User $user2;

    private User $user3;

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

        // Create test users
        $this->user1 = User::create('user1@example.com', ['ROLE_USER']);
        $plainPassword1 = 'password123';
        $hashedPassword1 = $this->passwordHasher->hashPassword($this->user1, $plainPassword1);
        $this->user1->setPassword($hashedPassword1);

        $this->user2 = User::create('user2@example.com', ['ROLE_USER']);
        $plainPassword2 = 'password123';
        $hashedPassword2 = $this->passwordHasher->hashPassword($this->user2, $plainPassword2);
        $this->user2->setPassword($hashedPassword2);

        $this->user3 = User::create('user3@example.com', ['ROLE_USER']);
        $plainPassword3 = 'password123';
        $hashedPassword3 = $this->passwordHasher->hashPassword($this->user3, $plainPassword3);
        $this->user3->setPassword($hashedPassword3);

        $this->entityManager->persist($this->user1);
        $this->entityManager->persist($this->user2);
        $this->entityManager->persist($this->user3);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();

        static::ensureKernelShutdown();
    }

    public function test_list_users_returns_unauthorized_when_not_logged_in(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/api/users');

        self::assertResponseStatusCodeSame(401);
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];
        self::assertArrayHasKey('error', $response);
    }

    public function test_list_users_returns_other_users_when_logged_in(): void
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

        // Request /api/users
        $client->request('GET', '/api/users');

        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];

        self::assertArrayHasKey('users', $response);
        /** @var array<mixed> $users */
        $users = $response['users'] ?? [];
        self::assertCount(2, $users);

        // Verify other users are returned (not user1)
        $emails = array_map(function ($u) {
            self::assertIsArray($u);

            return $u['email'] ?? null;
        }, $users);
        self::assertContains('user2@example.com', $emails);
        self::assertContains('user3@example.com', $emails);
        self::assertNotContains('user1@example.com', $emails);
    }

    public function test_list_users_returns_correct_structure(): void
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

        // Request /api/users
        $client->request('GET', '/api/users');

        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];
        /** @var array<mixed> $users */
        $users = $response['users'] ?? [];

        // Verify each user has id and email
        foreach ($users as $user) {
            self::assertIsArray($user);
            self::assertArrayHasKey('id', $user);
            self::assertArrayHasKey('email', $user);
            self::assertNotEmpty($user['id']);
            self::assertNotEmpty($user['email']);
        }
    }

    public function test_list_users_excludes_current_user(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // Login as user2
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'user2@example.com',
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Request /api/users
        $client->request('GET', '/api/users');

        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];
        /** @var array<mixed> $users */
        $users = $response['users'] ?? [];

        // Verify user2 is not in the list
        $emails = array_map(function ($u) {
            self::assertIsArray($u);

            return $u['email'] ?? null;
        }, $users);
        self::assertNotContains('user2@example.com', $emails);
        self::assertContains('user1@example.com', $emails);
        self::assertContains('user3@example.com', $emails);
    }
}
