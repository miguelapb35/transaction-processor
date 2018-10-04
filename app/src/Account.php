<?php
declare(strict_types=1);
namespace App;

class Account
{
    const PRIVATE = 'natural';
    const BUSINESS = 'legal';

    private $id;
    private $type;

    public function __construct(string $id, string $type)
    {
        $this->id = $id;
        $this->validateType($type);
        $this->type = $type;
    }

    public function validateType(string $type): void
    {
        if ($type !== self::PRIVATE && $type !== self::BUSINESS) {
            throw new \InvalidArgumentException();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    // Create methods like cashIn() and cashOut() ...
}
