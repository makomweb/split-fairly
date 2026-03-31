<?php

namespace App\Tests\Integration\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReportControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

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

        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Event')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Report')->execute();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Report')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Event')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();
        static::ensureKernelShutdown();
    }

    public function test_initiate_accepts_id_query_parameter(): void
    {
        $client = static::createClient();
        $this->loginUser($client, 'user1@example.com');

        $testId = 'abc123def456';
        $client->request('POST', "/api/report/calculation?id={$testId}");

        self::assertResponseStatusCodeSame(202);
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('id', $response);
    }

    private function loginUser($client, string $email): void
    {
        $user = User::create($email, ['ROLE_USER']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        static::ensureKernelShutdown();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => $email,
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();
    }
}
