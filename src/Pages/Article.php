<?php

namespace Pages;

use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;
use Tags\Tag;
use Users\User;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="article",
 *      indexes={
 *          @Index(name="published_at", columns={"published_at"})
 *      }
 * )
 *
 */
class Article
{
    use Identifier;
    use MagicAccessors;

    const PRESENTER = 'Pages:Front:Page';
    const PRESENTER_ACTION = 'show';

    const LENGTH_TITLE = 255;
    const LENGTH_INTRO = 500;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false, unique=true)
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(name="intro", type="string", length=500, nullable=false, unique=false)
     * @var string
     */
    protected $intro;

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
     * @ORM\Column(name="is_published", type="boolean", nullable=false, unique=false, options={"default": false})
     * @var bool
     */
    private $isPublished = false;

    /**
     * @ORM\Column(name="published_at", type="datetime", nullable=true, unique=false)
     * @var \DateTime
     */
    private $publishedAt;

    /**
     * @ORM\ManyToMany(targetEntity="Tags\Tag")
     * @var ArrayCollection
     */
    private $tags;

    public function __construct(
        $title,
        $intro,
        $text,
        User $author
    ) {
        $this->setTitle($title);
        $this->setIntro($intro);
        $this->setText($text);
        $this->author = $author;

        $this->createdAt = new \DateTime('now');

        $this->tags = new ArrayCollection;
    }

    public function publish(\DateTime $publishTime)
    {
        $this->isPublished = true;
        $this->publishedAt = $publishTime;
    }

    public function confine()
    {
        $this->isPublished = false;
    }

    public static function getCacheKey($id)
    {
        return self::class . '/' . $id;
    }


    /*
     * -----------------------
     * ----- COLLECTIONS -----
     * -----------------------
     */

    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);
    }


    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        Validators::assert($title, 'unicode:1..'.self::LENGTH_TITLE);
        $this->title = $title;
    }

    /**
     * @param string $intro
     */
    public function setIntro($intro)
    {
        Validators::assert($intro, 'unicode:1..'.self::LENGTH_INTRO);
        $this->intro = $intro;
    }

    /**
     * @param string $text
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


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */

    /**
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }


}