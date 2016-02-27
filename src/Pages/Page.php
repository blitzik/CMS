<?php

namespace Pages;

use Pages\Exceptions\Logic\DateTimeFormatException;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;
use Users\User;
use Tags\Tag;
use Url\Url;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="page",
 *      indexes={
 *          @Index(name="published_at", columns={"published_at"})
 *      }
 * )
 *
 */
class Page
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
     * @ORM\ManyToOne(targetEntity="Users\User")
     * @ORM\JoinColumn(name="author", referencedColumnName="id", nullable=false)
     * @var \Users\User
     */
    private $author;

    /**
     * @ORM\OneToOne(targetEntity="Url\Url")
     * @ORM\JoinColumn(name="url", referencedColumnName="id", nullable=false)
     * @var Url
     */
    private $url;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="is_published", type="boolean", nullable=false, unique=false, options={"default": false})
     * @var bool
     */
    private $isPublished;

    /**
     * @ORM\Column(name="published_at", type="datetime", nullable=true, unique=false)
     * @var \DateTime
     */
    protected $publishedAt;
    
    /**
     * @ORM\Column(name="allowed_comments", type="boolean", nullable=false, unique=false, options={"default": true})
     * @var bool
     */
    protected $allowedComments;

    /**
     * @ORM\ManyToMany(targetEntity="Tags\Tag")
     * @var ArrayCollection
     */
    private $tags;


    public function __construct(
        $title,
        $intro,
        $text,
        Url $url,
        User $author
    )
    {
        $this->setTitle($title);
        $this->setIntro($intro);
        $this->setText($text);
        $this->setUrl($url);
        $this->author = $author;

        $this->isPublished = false;
        $this->allowedComments = true;

        $this->createdAt = new \DateTime('now');

        $this->tags = new ArrayCollection;
    }


    /*
     * -----------------------
     * ----- COLLECTIONS -----
     * -----------------------
     */

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);
    }


    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }


    public function clearTags()
    {
        $this->tags->clear();
    }


    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags->toArray();
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
        Validators::assert($title, 'unicode:1..' . self::LENGTH_TITLE);
        $this->title = $title;
    }


    /**
     * @param string $intro
     */
    public function setIntro($intro)
    {
        Validators::assert($intro, 'unicode:1..' . self::LENGTH_INTRO);
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
     * @param Url $url
     */
    public function setUrl(Url $url)
    {
        $this->url = $url;
    }


    /**
     * @param \DateTime|null $publishTime
     * @throws DateTimeFormatException
     */
    public function setPublishedAt($publishTime)
    {
        if (!$publishTime instanceof \DateTime) {
            try {
                $publishTime = new \DateTime($publishTime);
            } catch (\Exception $e) {
                throw new DateTimeFormatException;
            }
        }

        $this->publishedAt = $publishTime;
    }


    /**
     * @param bool $isPublished
     */
    public function setArticleVisibility($isPublished)
    {
        $this->isPublished = (bool)$isPublished;
    }


    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }


    /**
     * @param bool $allowedComments
     */
    public function setAllowedComments($allowedComments)
    {
        $this->allowedComments = (bool)$allowedComments;
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
     * @return bool
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }


    public function getAllowedComments()
    {
        return (bool)$this->allowedComments;
    }


    /*
     * -----------------------
     * ----- URL GETTERS -----
     * -----------------------
     */


    /**
     * @return int
     */
    public function getUrlId()
    {
        return $this->url->getId();
    }


    /**
     * @return string
     */
    public function getUrlPath()
    {
        return $this->url->urlPath;
    }


    /*
     * ------------------------
     * ----- USER GETTERS -----
     * ------------------------
     */


    public function getAuthorId()
    {
        return $this->author->getId();
    }

}