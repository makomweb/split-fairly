<?php

namespace App\Tests\Integration\API;

use App\Entity\Event;
use App\Entity\User;
use App\SplitFairly\Expense;
use App\SplitFairly\Price;
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

        $user1 = User::create('user1@example.com', ['ROLE_USER']);
        $hashedPassword = $passwordHasher->hashPassword($user1, 'password123');
        $user1->setPassword($hashedPassword);

        $user2 = User::create('user2@example.com', ['ROLE_USER']);
        $hashedPassword2 = $passwordHasher->hashPassword($user2, 'password123');
        $user2->setPassword($hashedPassword2);

        $entityManager->persist($user1);
        $entityManager->persist($user2);
        $entityManager->flush();

        $this->createExpenseEvent($entityManager, $user1, $user2);
        $this->createExpenseEvent($entityManager, $user2, $user1);

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

    private function createExpenseEvent(EntityManagerInterface $entityManager, User $paidBy, User $forUser): void
    {
        $expense = new Expense(
            price: new Price(50.00, 'EUR'),
            what: 'Test Expense',
            type: 'Groceries',
            location: 'Test Location'
        );

        $event = new Event(
            createdBy: $paidBy->getUuid()->toRfc4122(),
            subjectType: 'Expense',
            subjectId: $expense->getId()->toRfc4122(),
            eventType: 'tracked',
            payload: [
                'price' => ['value' => $expense->price->value, 'currency' => $expense->price->currency],
                'what' => $expense->what,
                'type' => $expense->type,
                'location' => $expense->location,
            ]
        );

        $entityManager->persist($event);
        $entityManager->flush();
    }
}
