<?php

namespace Images;

use Images\Exceptions\Runtime\FileNameException;
use Images\Exceptions\Runtime\NotImageUploadedException;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="image",
 *      indexes={
 *          @Index(name="uploaded_at", columns={"uploaded_at"})
 *      }
 * )
 *
 */
class Image
{
    use MagicAccessors;

    const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1MB

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string", length=36, nullable=false, options={"fixed": true})
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(name="original_name", type="string", length=255, nullable=false, unique=false)
     * @var string
     */
    private $originalName;

    /**
     * @ORM\Column(name="extension", type="string", length=4, nullable=false, unique=false)
     * @var string
     */
    private $extension;

    /**
     * @ORM\Column(name="width", type="smallint", nullable=false, unique=false)
     * @var int
     */
    private $width;

    /**
     * @ORM\Column(name="height", type="smallint", nullable=false, unique=false)
     * @var int
     */
    private $height;

    /**
     * @ORM\Column(name="file_size", type="integer", nullable=false, unique=false)
     * @var int
     */
    private $fileSize;

    /**
     * @ORM\Column(name="uploaded_at", type="datetime", nullable=false, unique=false)
     * @var \DateTime
     */
    private $uploadedAt;


    public function __construct(
        FileUpload $file
    ) {
        if (!$file->isImage()) {
            throw new NotImageUploadedException;
        }

        $this->id = Uuid::uuid4();

        $pathInfo = pathinfo($file->getSanitizedName());
        if (!isset($pathInfo['extension'])) {
            throw new FileNameException('Filename must have extension');
        }

        $this->extension = $pathInfo['extension'];
        $this->originalName = $pathInfo['filename'];

        $imgSize = $file->getImageSize();
        $this->width = $imgSize[0];
        $this->height = $imgSize[1];

        $this->fileSize = $file->getSize();

        $this->uploadedAt = new \DateTime('now');
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }


    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }


    /**
     * @return \DateTime
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }


    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }


    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }


    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }


    /**
     * @return string
     */
    public function getComposedFilePath()
    {
        return sprintf(
            '%s/%s.%s',
            $this->id,
            Strings::webalize($this->originalName),
            $this->extension
        );
    }

}