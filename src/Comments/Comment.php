<?php

namespace Components;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\Table(name="comment")
 *
 */
class Comment
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\Column(name="author_name", type="string", length=25, nullable=false, unique=false)
     * @var string
     */
    private $authorName;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    protected $createdAt;



    public function __construct(

    ) {

    }
}