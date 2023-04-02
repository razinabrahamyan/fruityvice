<?php

namespace App\Command;

use App\Entity\Fruit;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'fruits:fetch',
    description: 'Add a short description for your command',
)]
class FruitsFetchCommand extends Command
{
    protected static $defaultDescription = 'Creates a fruits list.';

    public function __construct(
        ManagerRegistry $doctrine,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher
    ){
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->passwordHasher = $passwordHasher;
    }


    protected function configure(): void
    {
        $this
            ->setHelp('This command getting all fruits from https://fruityvice.com/ and saving them into local DB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->note([
            'Fruity Creator',
            '==============',
            ''
        ]);

        $fruits = $this->getFruits();
        $io->note([
            'Get all fruits',
            '==============',
        ]);
        $entityManager = $this->doctrine->getManager();
        foreach (json_decode($fruits) as $fruit){
            $newFruit = new Fruit();
            $newFruit->setGenus($fruit->genus);
            $newFruit->setName($fruit->name);
            $newFruit->setFamily($fruit->family);
            $newFruit->setOrders($fruit->order);
            $newFruit->setNutritions([
                'carbohydrates' => $fruit->nutritions->carbohydrates,
                'protein' => $fruit->nutritions->protein,
                'fat' => $fruit->nutritions->fat,
                'calories' => $fruit->nutritions->calories,
                'sugar' => $fruit->nutritions->sugar,
            ]);
            $entityManager->persist($newFruit);
            $entityManager->flush();
        }

        $io->success([
            'Add all fruits in table',
        ]);
        $email = (new Email())
            ->from('From@mail')
            ->to('to@mail')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>All fruit added </p>');

        $this->mailer->send($email);
        $io->success([
            'Send email',
        ]);
        $user = new User();
        $user->setEmail('user@gmail.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, '1234'));
        $entityManager->persist($user);
        $entityManager->flush();
        $io->success([
            'Add new user email user@gmail.com, pass - 1234',
        ]);
        return Command::SUCCESS;
    }
    protected function getFruits(){
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://fruityvice.com/api/fruit/all');
        return $response->getContent();
    }
}
