<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Question;
use App\Entity\Comment;
use App\Entity\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 👤 Création des utilisateurs
        $users = [];
        foreach ([
                     ['Alice', 'Durand', 'alice@example.com'],
                     ['Bob', 'Martin', 'bob@example.com'],
                     ['Carla', 'Lopez', 'carla@example.com'],
                     ['Dan', 'Petit', 'dan@example.com'],
                 ] as $i => [$firstname, $lastname, $email]) {
            $user = new User();
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setPicture('uploads/profiles/default.jpg');
            $manager->persist($user);
            $users[] = $user;
        }

        // 🔁 Relations de follow
        $users[0]->addFollowedUser($users[1]); // Alice suit Bob
        $users[0]->addFollowedUser($users[2]); // Alice suit Carla
        $users[1]->addFollowedUser($users[0]); // Bob suit Alice
        $users[2]->addFollowedUser($users[3]); // Carla suit Dan
        $users[3]->addFollowedUser($users[0]); // Dan suit Alice

        // ❓ Questions
        $questions = [];
        $questions[] = (new Question())
            ->setTitle('Pourquoi utiliser Symfony ?')
            ->setContent('Est-ce mieux que Laravel ?')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRating(3)
            ->setNbrOfResponse(2)
            ->setAuthor($users[0]);

        $questions[] = (new Question())
            ->setTitle('Comment sécuriser une API ?')
            ->setContent('JWT ou OAuth2 ?')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRating(1)
            ->setNbrOfResponse(1)
            ->setAuthor($users[1]);

        $questions[] = (new Question())
            ->setTitle('Meilleures pratiques en Doctrine ?')
            ->setContent('Quelles relations éviter ?')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRating(5)
            ->setNbrOfResponse(3)
            ->setAuthor($users[2]);

        foreach ($questions as $question) {
            $manager->persist($question);
        }

        // 💬 Commentaires
        $comments = [];
        $comments[] = (new Comment())
            ->setContent('Symfony est top pour les apps complexes.')
            ->setRating(2)
            -> setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($users[1])
            ->setQuestion($questions[0]);

        $comments[] = (new Comment())
            ->setContent('Utilise OAuth si tu as plusieurs services.')
            ->setRating(1)
            -> setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($users[0])
            ->setQuestion($questions[1]);

        $comments[] = (new Comment())
            ->setContent('Évite les relations ManyToMany sans entité pivot.')
            ->setRating(3)
            -> setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($users[3])
            ->setQuestion($questions[2]);

        foreach ($comments as $comment) {
            $manager->persist($comment);
        }

        // 👍 Votes
        $votes = [];
        $votes[] = (new Vote())->setIsLiked(true)->setAuthor($users[0])->setComment($comments[0]);
        $votes[] = (new Vote())->setIsLiked(false)->setAuthor($users[2])->setComment($comments[0]);
        $votes[] = (new Vote())->setIsLiked(true)->setAuthor($users[1])->setComment($comments[2]);

        foreach ($votes as $vote) {
            $manager->persist($vote);
        }

        $manager->flush();
    }
}
