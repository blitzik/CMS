<?php

namespace Pages;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;
use Users\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="article")
 *
 */
class Article
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false, unique=true)
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(name="text", type="text", nullable=false, unique=false)
     * @var string
     */
    protected $text;

    /**
     * @ORM\ManyToOne(targetEntity="\Users\User")
     * @ORM\JoinColumn(name="author", referencedColumnName="id", nullable=false)
     * @var \Users\User
     */
    private $author;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="is_released", type="boolean", nullable=false, unique=false, options={"default": false})
     * @var bool
     */
    private $isReleased = false;

    /**
     * @ORM\Column(name="released", type="datetime", nullable=true, unique=false)
     * @var \DateTime
     */
    private $releasedAt;


    public function __construct(
        $title,
        $text,
        User $author
    ) {
        $this->setTitle($title);
        $this->setText($text);
        $this->author = $author;

        $this->createdAt = new \DateTime('now');
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        Validators::assert($title, 'unicode:1..255');
        $this->title = $title;
    }

    /**
     * @param $text
     */
    public function setText($text)
    {
        Validators::assert($text, 'unicode');
        $this->text = $text;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function release()
    {
        $this->isReleased = true;
        $this->releasedAt = new \DateTime('now');
    }

    public function confine()
    {
        $this->isReleased = false;
    }

    /**
     * @return \DateTime
     */
    public function getReleasedAt()
    {
        return $this->releasedAt;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

}