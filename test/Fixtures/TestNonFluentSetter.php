<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

final class TestNonFluentSetter
{
    /** @var string */
    public $lastname;

    /** @var string */
    private $email;

    /** @var string */
    private $firstName;

    public function __construct()
    {
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }
}
