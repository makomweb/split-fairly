<?php

namespace App\Tests\Integration\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CalculateExpensesControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = $this->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        \assert($entityManager instanceof EntityManagerInterface);
        \assert($passwordHasher instanceof UserPasswordHasherInterface);

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Event')->execute();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Event')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();
        static::ensureKernelShutdown();
    }

    public function test_calculate_response_contains_id(): void
    {
        $client = static::createClient();
        $this->loginUser($client, 'user1@example.com');

        $client->request('GET', '/api/calculate');

        self::assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('id', $response);
        self::assertIsString($response['id']);
        self::assertEquals(64, strlen($response['id']));
    }

    public function test_calculate_returns_consistent_id(): void
    {
        $client = static::createClient();
        $this->loginUser($client, 'user1@example.com');

        $client->request('GET', '/api/calculate');
        $response1 = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/api/calculate');
        $response2 = json_decode($client->getResponse()->getContent(), true);

        self::assertEquals($response1['id'], $response2['id']);
    }

    private function loginUser($client, string $email): void
    {
        $user = User::create($email, ['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => $email,
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();
    }
}
