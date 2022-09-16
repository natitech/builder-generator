<?php

namespace Nati\BuilderGenerator\Test\Fixtures;

final class TestPublic
{
    public  $test;

    /**
     * @var integer
     */
    public  $test4;

    /**
     * @var float
     */
    public  $test2;

    private $test3;

    /**
     * @var string|null
     * @ORM\Column(type="string", name="test", length=255, nullable=true)
     */
    public $address;

    /**
     * @var string|null
     */
    #[Attribute\Address(name: 'address')]
    private ?string $address2;

    public ?\DateTimeImmutable $test5;
}
