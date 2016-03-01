<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 24.02.2016
 */

namespace Comments;

use Pages\Exceptions\Runtime\WrongPageCommentReaction;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Validators;
use Pages\Page;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="comment",
 *     indexes={
 *         @Index(name="page_is_hidden", columns={"page", "is_hidden"})
 *     }
 * )
 */
class Comment
{
    use Identifier;
    use MagicAccessors;

    const LENGTH_AUTHOR = 25;
    const LENGTH_TEXT = 65535;

    /**
     * @ORM\Column(name="author", type="string", length=25, nullable=false, unique=false)
     * @var string
     */
    private $author;

    /**
     * @ORM\Column(name="text", type="text", length=65535, nullable=false, unique=false)
     * @var string
     */
    protected $text;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Pages\Page")
     * @ORM\JoinColumn(name="page", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var Page
     */
    private $page;

    /**
     * @ORM\Column(name="is_silenced", type="boolean", nullable=false, unique=false, options={"default": false})
     * @var bool
     */
    private $isSilenced;

    /**
     * @ORM\Column(name="is_hidden", type="boolean", nullable=false, unique=false, options={"default": false})
     * @var bool
     */
    private $isHidden;

    /**
     * @ORM\ManyToMany(targetEntity="Comment")
     * @ORM\JoinTable(
     *     name="comment_reactions",
     *     joinColumns={@JoinColumn(name="reaction", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@JoinColumn(name="comment", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $reactions;


    public function __construct(
        $author,
        $text,
        Page $page
    ) {
        $this->setAuthor($author);
        $this->setText($text);
        $this->page = $page;

        $this->isSilenced = false;
        $this->isHidden = false;

        $this->created = new \DateTime('now');

        $this->reactions = new ArrayCollection();
    }


    /**
     * @param string $text
     */
    public function setText($text)
    {
        Validators::assert($text, 'unicode:1..' . self::LENGTH_TEXT);

        $this->text = $text;
    }


    /**
     * @param string $author
     */
    private function setAuthor($author)
    {
        Validators::assert($author, 'unicode:1..' . self::LENGTH_AUTHOR);

        $this->author = $author;
    }


    public function silence()
    {
        $this->isSilenced = true;
    }


    public function release()
    {
        $this->isSilenced = false;
    }


    public function hide()
    {
        $this->isHidden = true;
    }


    public function show()
    {
        $this->isHidden = false;
    }


    /*
     * --------------------------------
     * ----- REACTIONS COLLECTION -----
     * --------------------------------
     */


    /**
     * @param Comment $reaction
     */
    public function addReaction(Comment $reaction)
    {
        if ($reaction->getPageId() !== $this->page->getId()) {
            throw new WrongPageCommentReaction;
        }

        $this->reactions->add($reaction);
    }


    /**
     * @return array
     */
    public function getReactions()
    {
        return $this->reactions->toArray();
    }


    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }


    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * @return bool
     */
    public function isSilenced()
    {
        return $this->isSilenced;
    }


    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->isHidden;
    }


    /*
     * ------------------------
     * ----- PAGE GETTERS -----
     * ------------------------
     */


    public function getPageId()
    {
        return $this->page->getId();
    }


    public function getPageAuthorId()
    {
        return $this->page->getAuthorId();
    }
}