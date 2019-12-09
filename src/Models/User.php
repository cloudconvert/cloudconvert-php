<?php


namespace CloudConvert\Models;


class User
{

    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var int
     */
    protected $credits;
    /**
     * @var \DateTimeImmutable
     */
    protected $created_at;

    /**
     * @var array
     */
    protected $links;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }


    /**
     * @return int
     */
    public function getCredits(): int
    {
        return $this->credits;
    }


    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }


    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }


}
