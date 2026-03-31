<?php

namespace App\Tests\Integration\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CalculateExpensesControllerTest extends WebTestCase
{
    public function test_calculate_response_contains_id(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
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

        $client->request('GET', '/api/calculate');

        self::assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('id', $response);
        self::assertIsString($response['id']);
        self::assertEquals(64, strlen($response['id']));
    }
}
