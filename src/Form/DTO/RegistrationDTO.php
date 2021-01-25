<?php

namespace App\Form\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RegistrationDTO
{
    /**
     * @Assert\NotBlank()
     */
    public ?string $username = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public ?string $email = null;

    public ?string $password = null;

    /**
     * @Assert\Callback()
     */
    public function validatePassword(ExecutionContextInterface $context): void
    {
        if (!$this->password) {
            $context
                ->buildViolation((new Assert\NotBlank())->message)
                ->atPath('password')
                ->addViolation()
            ;

            return;
        }

        if (\mb_strlen($this->password) < 8
            || !\preg_match('~[a-z]~', $this->password)
            || !\preg_match('~[A-Z]~', $this->password)
            || !\preg_match('~\d~', $this->password)
        ) {
            $context
                ->buildViolation('This value is not a valid password.')
                ->atPath('password')
                ->addViolation()
            ;
        }
    }
}