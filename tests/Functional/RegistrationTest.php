<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationTest extends WebTestCase
{
    public function test_guest_can_authenticate(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        static::assertResponseStatusCodeSame(200);

        $client->submitForm('Envoyer', $data = [
            'register[username]' => 'foobar',
            'register[email]' => 'foo@bar.com',
            'register[password]' => 'Foobarz1',
        ]);

        static::assertResponseRedirects('/', 302);
        $client->followRedirect();
        static::assertResponseStatusCodeSame(200);
        static::assertSelectorTextContains('#flash_messages .flash_success', 'Bienvenue sur Twittlior !');

        $user = static::$container->get(UserRepository::class)->findOneBy(['username' => $data['register[username]']]);
        static::assertInstanceOf(User::class, $user);
        static::assertSame($user->getUsername(), $data['register[username]']);
        static::assertSame($user->getEmail(), $data['register[email]']);

        $encoder = static::$container->get(UserPasswordEncoderInterface::class);
        static::assertTrue($encoder->isPasswordValid($user, $data['register[password]']));
    }

    public function test_invalid_blank_fields(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        static::assertResponseStatusCodeSame(200);

        $client->submitForm('Envoyer', [
            'register[username]' => '',
            'register[email]' => '',
            'register[password]' => '',
        ]);

        static::assertResponseStatusCodeSame(400);

        static::assertSelectorTextSame('label[for="register_username"] + ul > li', 'This value should not be blank.');
        static::assertSelectorTextSame('label[for="register_email"] + ul > li', 'This value should not be blank.');
        static::assertSelectorTextSame('label[for="register_password"] + ul > li', 'This value should not be blank.');
    }

    public function test_invalid_email_field(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        static::assertResponseStatusCodeSame(200);

        $client->submitForm('Envoyer', [
            'register[username]' => 'test',
            'register[email]' => 'invalid_email',
            'register[password]' => 'test',
        ]);

        static::assertResponseStatusCodeSame(400);

        static::assertSelectorTextSame('label[for="register_email"] + ul > li', 'This value is not a valid email address.');
    }

    /**
     * @dataProvider provideInvalidPasswords
     */
    public function test_invalid_password_field(string $password): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        static::assertResponseStatusCodeSame(200);

        $client->submitForm('Envoyer', [
            'register[username]' => 'test',
            'register[email]' => 'foo@bar.com',
            'register[password]' => $password,
        ]);

        static::assertResponseStatusCodeSame(400);

        static::assertSelectorTextSame('label[for="register_password"] + ul > li', 'This value is not a valid password.');
    }

    public function provideInvalidPasswords(): \Generator
    {
        yield ['123'];
        yield ['abc'];
        yield ['aB1,@'];
        yield ['abcdefgh'];
        yield ['abcdefg1'];
        yield ['ABCDEFG'];
        yield ['ABCDe1💣'];
    }
}
