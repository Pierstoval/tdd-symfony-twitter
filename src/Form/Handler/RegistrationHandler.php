<?php

namespace App\Form\Handler;

use App\Entity\User;
use App\Form\DTO\RegistrationDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationHandler
{
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $encoder;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->em = $em;
        $this->encoder = $encoder;
    }

    public function __invoke(RegistrationDTO $dto): User
    {
        $user = User::fromRegistration($dto, [$this->encoder, 'encodePassword']);

        $this->em->persist($user);
        $this->em->flush();

        $this->sendEmail($user);

        return $user;
    }

    private function sendEmail(User $user)
    {
        $url = $user->getId();

        $text = <<<EMAIL_TEXT
        Salut {$user->getUsername()} !
        
        Ton lien est celui-ci : <a href="{$url}">{$url}</a>
        EMAIL_TEXT;
    }
}