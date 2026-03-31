<?php

namespace App\Tests\Integration\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiMeEndpointTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    private UserPasswordHasherInterface $passwordHasher;

    private User $testUser;

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

        // Create a test user
        $this->testUser = User::create('test@example.com', ['ROLE_USER']);
        $plainPassword = 'testpassword123';
        $hashedPassword = $this->passwordHasher->hashPassword($this->testUser, $plainPassword);
        $this->testUser->setPassword($hashedPassword);

        $this->entityManager->persist($this->testUser);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();

        static::ensureKernelShutdown();
    }

    public function test_me_endpoint_returns_unauthorized_when_not_logged_in(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];
        self::assertArrayHasKey('error', $response);
    }

    public function test_me_endpoint_returns_user_data_when_logged_in(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // First login
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'test@example.com',
            '_password' => 'testpassword123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Now request /api/me
        $client->request('GET', '/api/me');

        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];
        self::assertArrayHasKey('user', $response);
        self::assertEquals('test@example.com', $response['user'] ?? null);
    }

    public function test_me_endpoint_with_different_users(): void
    {
        static::ensureKernelShutdown();
        // Create a second user
        $user2 = User::create('user2@example.com', ['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user2, 'password123');
        $user2->setPassword($hashedPassword);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();

        $client = static::createClient();

        // Login as first user
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'test@example.com',
            '_password' => 'testpassword123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Check /api/me returns first user
        $client->request('GET', '/api/me');

        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];
        self::assertEquals('test@example.com', $response['user'] ?? null);
    }

    public function test_me_endpoint_after_logout(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // Login
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'test@example.com',
            '_password' => 'testpassword123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Verify logged in
        $client->request('GET', '/api/me');
        self::assertResponseIsSuccessful();

        // Logout
        $client->request('GET', '/logout');

        // Try /api/me after logout
        $client->request('GET', '/api/me');
        self::assertResponseStatusCodeSame(401);
    }

    public function test_me_endpoint_with_admin_user(): void
    {
        static::ensureKernelShutdown();
        // Create admin user
        $adminUser = User::create('admin@example.com', ['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'adminpass123');
        $adminUser->setPassword($hashedPassword);
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $client = static::createClient();

        // Login as admin via regular login page
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'admin@example.com',
            '_password' => 'adminpass123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Try /api/me
        $client->request('GET', '/api/me');
        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $response = \is_string($responseContent) ? json_decode($responseContent, true) : null;
        $response = \is_array($response) ? $response : [];
        self::assertEquals('admin@example.com', $response['user'] ?? null);
    }
}
