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

        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = $this->getContainer()->get(UserPasswordHasherInterface::class);

        // Clean up before each test
        $this->entityManager->createQuery('DELETE FROM App:User')->execute();

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

        $this->entityManager->createQuery('DELETE FROM App:User')->execute();
        $this->entityManager->close();
    }

    public function test_me_endpoint_returns_unauthorized_when_not_logged_in(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/me');

        $this->assertResponseStatusCodeSame(401);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
    }

    public function test_me_endpoint_returns_user_data_when_logged_in(): void
    {
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

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $response);
        $this->assertEquals('test@example.com', $response['user']);
    }

    public function test_me_endpoint_with_different_users(): void
    {
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
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('test@example.com', $response['user']);
    }

    public function test_me_endpoint_after_logout(): void
    {
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
        $this->assertResponseIsSuccessful();

        // Logout
        $client->request('GET', '/logout');

        // Try /api/me after logout
        $client->request('GET', '/api/me');
        $this->assertResponseStatusCodeSame(401);
    }

    public function test_me_endpoint_with_admin_user(): void
    {
        // Create admin user
        $adminUser = User::create('admin@example.com', ['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'adminpass123');
        $adminUser->setPassword($hashedPassword);
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $client = static::createClient();

        // Login as admin via admin login page
        $crawler = $client->request('GET', '/admin/login');
        $form = $crawler->selectButton('Sign In to Admin')->form([
            '_username' => 'admin@example.com',
            '_password' => 'adminpass123',
        ]);
        $client->submit($form);

        // Try /api/me
        $client->request('GET', '/api/me');
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('admin@example.com', $response['user']);
    }
}
