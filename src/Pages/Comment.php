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
use Nette\Security\IResource;
use Nette\Utils\Validators;
use Pages\Page;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="comment",
 *     indexes={
 *         @Index(name="page_order_id", columns={"page", "order", "id"})
 *     }
 * )
 */
class Comment implements IResource
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
     * @ORM\Column(name="`order`", type="smallint", nullable=false, unique=false)
     * @var int
     */
    private $order;

    /**
     * @ORM\Column(name="ip_address", type="string", length=39, nullable=true, unique=false)
     * @var string
     */
    private $ipAddress;

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
        Page $page,
        $order,
        $ipAddress = null
    ) {
        $this->setAuthor($author);
        $this->setText($text);
        $this->page = $page;
        $this->setOrder($order);
        $this->setIpAddress($ipAddress);

        $this->isSilenced = false;

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


    public function setOrder($order)
    {
        Validators::assert($order, 'numericint:1..');
        $this->order = $order;
    }


    /**
     * @param string $ipAddress
     */
    private function setIpAddress($ipAddress)
    {
        Validators::assert($ipAddress, 'unicode|null');
        $this->ipAddress = $ipAddress;
    }


    public function silence()
    {
        $this->isSilenced = true;
    }


    public function release()
    {
        $this->isSilenced = false;
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
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }


    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }


    /*
     * ------------------------
     * ----- PAGE GETTERS -----
     * ------------------------
     */


    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->page->getId();
    }


    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->page->title;
    }


    /**
     * @return int
     */
    public function getPageAuthorId()
    {
        return $this->page->getAuthorId();
    }


    /**
     * @return bool
     */
    public function areCommentsClosed()
    {
        return !$this->page->getAllowedComments();
    }


    /**
     * @return bool
     */
    public function isPageDraft()
    {
        return $this->page->isDraft();
    }


    /*
     * ---------------------
     * ----- I_RESOURCE -----
     * ---------------------
     */


    function getResourceId()
    {
        return 'page_comment';
    }
}