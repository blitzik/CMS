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

/**
 * @ORM\Entity
 * @ORM\Table(name="comment")
 *
 */
class Comment
{
    use Identifier;
    use MagicAccessors;

    const LENGTH_AUTHOR = 50;
    const LENGTH_TEXT = 65535;

    /**
     * @ORM\Column(name="author", type="string", length=50, nullable=false, unique=false)
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
     * @ORM\ManyToOne(targetEntity="Page")
     * @ORM\JoinColumn(name="page", referencedColumnName="id", nullable=false)
     * @var Page
     */
    private $page;

    /**
     * @ORM\ManyToMany(targetEntity="Comment")
     * @ORM\JoinTable(
     *     name="comment_reactions",
     *     joinColumns={@JoinColumn(name="comment", referencedColumnName="id")},
     *     inverseJoinColumns={@JoinColumn(name="reaction", referencedColumnName="id")}
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

        $this->created = new \DateTime('now');

        $this->reactions = new ArrayCollection();
    }


    /**
     * @param string $author
     */
    private function setAuthor($author)
    {
        Validators::assert($author, 'unicode:1..' . self::LENGTH_AUTHOR);

        $this->author = $author;
    }


    /**
     * @param string $text
     */
    public function setText($text)
    {
        Validators::assert($text, 'unicode:1..' . self::LENGTH_TEXT);

        $this->text = $text;
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