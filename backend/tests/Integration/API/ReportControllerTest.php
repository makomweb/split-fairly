<?php

namespace App\Tests\Integration\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReportControllerTest extends WebTestCase
{
    public function test_initiate_accepts_id_query_parameter(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $user = User::create('user1@example.com', ['ROLE_USER']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign In')->form([
            '_username' => 'user1@example.com',
            '_password' => 'password123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        $testId = hash('sha256', bin2hex(random_bytes(16)));
        $client->request('POST', '/api/report/calculation?id=' . $testId);

        self::assertResponseStatusCodeSame(202);

        $content = $client->getResponse()->getContent();
        assert(is_string($content));

        $response = json_decode($content, true);
        assert(is_array($response));

        self::assertArrayHasKey('id', $response);
    }
}
