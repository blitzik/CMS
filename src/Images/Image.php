<?php

namespace Images;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Http\FileUpload;

/**
 * @ORM\Entity
 * @ORM\Table(name="image")
 *
 */
class Image
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string", length=36, nullable=false, options={"fixed": true})
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=false)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="location", type="string", nullable=false, unique=true)
     * @var string
     */
    private $location;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    private $createdAt;


    public function __construct(
        FileUpload $file,
        $fileName
    ) {

    }
}