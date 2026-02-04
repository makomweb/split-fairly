<?php

namespace App\Tests\Integration\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginFormFlowTest extends WebTestCase
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

        // Clean up after tests
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();

        static::ensureKernelShutdown();
    }

    public function test_app_login_page_is_accessible(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        self::assertStringContainsString('Welcome Back', \is_string($responseContent) ? $responseContent : '');
    }

    public function test_admin_login_page_is_accessible(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        self::assertStringContainsString('Admin Dashboard', \is_string($responseContent) ? $responseContent : '');
    }

    public function test_user_login_with_valid_credentials(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        // Submit the form with valid credentials
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'test@example.com',
            '_password' => 'testpassword123',
        ]);

        $client->submit($form);

        // Should redirect after successful login
        self::assertResponseRedirects('/', 302);

        // Follow redirect
        $client->followRedirect();

        // Should be able to access the SPA
        self::assertResponseIsSuccessful();
    }

    public function test_user_login_with_invalid_password(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'test@example.com',
            '_password' => 'wrongpassword',
        ]);

        $client->submit($form);

        // Should redirect back to login on invalid credentials
        self::assertResponseRedirects('/login', 302);

        // Follow redirect
        $client->followRedirect();

        // Should show error message
        $responseContent = $client->getResponse()->getContent();
        self::assertStringContainsString('Invalid credentials', \is_string($responseContent) ? $responseContent : '');
    }

    public function test_user_login_with_non_existent_email(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'nonexistent@example.com',
            '_password' => 'testpassword123',
        ]);

        $client->submit($form);

        // Should redirect back to login
        self::assertResponseRedirects('/login', 302);

        // Follow redirect
        $client->followRedirect();

        // Should show error
        $responseContent = $client->getResponse()->getContent();
        self::assertStringContainsString('Invalid credentials', \is_string($responseContent) ? $responseContent : '');
    }

    public function test_admin_login_with_admin_user(): void
    {
        static::ensureKernelShutdown();
        // Create an admin user
        $adminUser = User::create('admin@example.com', ['ROLE_ADMIN']);
        $plainPassword = 'adminpassword123';
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $plainPassword);
        $adminUser->setPassword($hashedPassword);

        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/login');

        $form = $crawler->selectButton('Sign In to Admin')->form([
            '_username' => 'admin@example.com',
            '_password' => 'adminpassword123',
        ]);

        $client->submit($form);

        // Should redirect to admin dashboard
        self::assertResponseRedirects('/admin', 302);
    }

    public function test_regular_user_cannot_access_admin_page(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        // Try to access admin directly (should redirect to login)
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login', 302);
    }

    public function test_session_is_created_after_successful_login(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'test@example.com',
            '_password' => 'testpassword123',
        ]);

        $client->submit($form);
        $client->followRedirect();

        // Check that a session cookie is set
        self::assertTrue($client->getResponse()->headers->has('Set-Cookie')
                         || count($client->getCookieJar()->all()) > 0);
    }

    public function test_remember_me_checkbox_preserves_session(): void
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'test@example.com',
            '_password' => 'testpassword123',
            '_remember_me' => true,
        ]);

        $client->submit($form);
        $client->followRedirect();

        // Check that remember-me cookie is set
        $cookies = $client->getCookieJar()->all();
        $rememberMeCookie = array_filter($cookies, fn ($cookie) => 'REMEMBERME' === $cookie->getName());
        self::assertNotEmpty($rememberMeCookie);
    }
}
