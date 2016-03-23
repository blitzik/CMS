<?php

namespace Pages;

use Pages\Exceptions\Runtime\PageIntroHtmlLengthException;
use Pages\Exceptions\Runtime\PagePublicationTimeException;
use Pages\Exceptions\Logic\DateTimeFormatException;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Security\IResource;
use Nette\Utils\Validators;
use Localization\Locale;
use Users\User;
use Tags\Tag;
use Url\Url;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="page",
 *      indexes={
 *          @Index(name="is_draft_published_at", columns={"is_draft", "published_at"})
 *      }
 * )
 *
 */
class Page implements IResource
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
     * @ORM\Column(name="intro_html", type="string", length=3000, nullable=false, unique=false)
     * @var string
     */
    private $introHtml;

    /**
     * @ORM\Column(name="text", type="text", nullable=true, unique=false)
     * @var string
     */
    protected $text;

    /**
     * @ORM\Column(name="text_html", type="text", nullable=true, unique=false)
     * @var string
     */
    private $textHtml;

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
     * @ORM\Column(name="meta_description", type="text", length=65535, nullable=true, unique=false)
     * @var string
     */
    protected $metaDescription;

    /**
     * @ORM\Column(name="meta_keywords", type="text", length=65535, nullable=true, unique=false)
     * @var string
     */
    protected $metaKeywords;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="is_draft", type="boolean", nullable=false, unique=false, options={"default": true})
     * @var bool
     */
    private $isDraft;

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
     * @ORM\ManyToOne(targetEntity="Localization\Locale")
     * @ORM\JoinColumn(name="locale", referencedColumnName="id", nullable=false)
     * @var Locale
     */
    private $locale;

    /**
     * @ORM\ManyToMany(targetEntity="Tags\Tag", indexBy="id")
     * @var ArrayCollection
     */
    private $tags;


    public function __construct(
        $title,
        $intro,
        $text,
        Url $url,
        User $author,
        Locale $locale
    ) {
        $this->setTitle($title);
        $this->setIntro($intro);
        $this->setText($text);
        $this->setUrl($url);
        $this->author = $author;

        $this->locale = $locale;

        $this->isDraft = true;
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
     * @param string $introHtml
     * @throws PageIntroHtmlLengthException
     */
    public function setIntroHtml($introHtml)
    {
        Validators::assert($introHtml, 'unicode');
        if (!Validators::is($introHtml, 'unicode:1..3000')) {
            throw new PageIntroHtmlLengthException;
        }

        $this->introHtml = $introHtml;
    }


    /**
     * @param string|null $text
     */
    public function setText($text)
    {
        Validators::assert($text, 'unicode|null');
        $this->text = $text;
    }


    /**
     * @param string|null $textHtml
     */
    public function setTextHtml($textHtml)
    {
        Validators::assert($textHtml, 'unicode|null');
        $this->textHtml = $textHtml;
    }


    /**
     * @param Url $url
     */
    public function setUrl(Url $url)
    {
        $this->url = $url;
    }


    /**
     * @param string $description
     */
    public function setMetaDescription($description)
    {
        Validators::assert($description, 'unicode|null');
        $this->metaDescription = $description;
    }


    /**
     * @param string $keywords
     */
    public function setMetaKeywords($keywords)
    {
        Validators::assert($keywords, 'unicode|null');
        $this->metaKeywords = $keywords;
    }


    /**
     * @param \DateTime $publishTime
     * @throws DateTimeFormatException
     * @throws PagePublicationTimeException
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

        if (!$this->isDraft() and $publishTime > (new \DateTime('now'))) {
            throw new PagePublicationTimeException;
        }

        $this->publishedAt = $publishTime;
    }

    /**
     * @param \DateTime $publishTime
     * @throws DateTimeFormatException
     * @throws PagePublicationTimeException
     */
    public function setAsPublished($publishTime)
    {
        $this->setPublishedAt($publishTime);
        $this->isDraft = false;
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
    public function isDraft()
    {
        return $this->isDraft;
    }


    public function getAllowedComments()
    {
        return $this->allowedComments;
    }


    /**
     * @return string
     */
    public function getIntroHtml()
    {
        return $this->introHtml;
    }


    /**
     * @return string
     */
    public function getTextHtml()
    {
        return $this->textHtml;
    }


    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }


    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }


    /*
     * --------------------------
     * ----- LOCALE GETTERS -----
     * --------------------------
     */


    public function getLocaleCode()
    {
        return $this->locale->getCode();
    }


    /**
     * @return string
     */
    public function getLocaleName()
    {
        return $this->locale->getName();
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
        return $this->url->getCurrentUrlPath();
    }


    /*
     * ------------------------
     * ----- USER GETTERS -----
     * ------------------------
     */


    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->author->getId();
    }


    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->author->getName();
    }


    /*
     * ---------------------
     * ----- I_RESOURCE -----
     * ---------------------
     */


    function getResourceId()
    {
        return 'page';
    }


}