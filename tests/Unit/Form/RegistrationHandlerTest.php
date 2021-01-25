<?php

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\DTO\RegistrationDTO;
use App\Form\Handler\RegistrationHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationHandlerTest extends TestCase
{
    public function test_successful_registration(): void
    {
        $insertedUser = (object) [
            'user' => null,
        ];

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with(self::callback(static function ($object) use ($insertedUser) {
            $insertedUser->user = $object;
            return $object instanceof User;
        }));
        $em->expects(self::once())->method('flush')->willReturnCallback(static function () use ($insertedUser) {
            if (!$insertedUser->user) {
                static::fail('No user was flushed');
            }

            (function(){$this->id = 1;})->bindTo($insertedUser->user, User::class)();

            return true;
        });

        $encoder = $this->createMock(UserPasswordEncoderInterface::class);
        $encoder
            ->expects(self::once())
            ->method('encodePassword')
            ->willReturnCallback(static function (User $user, string $password) {
                return $password.'+1';
            })
        ;

        $dto = new RegistrationDTO();
        $dto->username = 'foobar';
        $dto->email = 'foo@bar.com';
        $dto->password = 'foobar';

        $user = (new RegistrationHandler($em, $encoder))($dto);

        static::assertSame($dto->username, $user->getUsername());
        static::assertSame($dto->email, $user->getEmail());
        static::assertSame($dto->password.'+1', $user->getPassword());
        static::assertSame(1, $user->getId());
    }
}