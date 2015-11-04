<?php

namespace Images;

use App\Exceptions\Runtime\FileNameException;
use App\Exceptions\Runtime\NotImageUploadedException;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Http\FileUpload;
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
    const UPLOAD_DIRECTORY = __DIR__ . '/../../uploads/images/';

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

        $name = $file->getSanitizedName();

        $this->extension = $this->separateExtension($name);
        $this->originalName = \mb_substr($name, 0, ((-1) * \mb_strlen($this->extension) - 1));

        $imgSize = $file->getImageSize();
        $this->width = $imgSize[0];
        $this->height = $imgSize[1];

        $this->uploadedAt = new \DateTime('now');
    }

    /**
     * Returns extension including e.g. [jpg]
     *
     * @param $filename
     * @return string
     */
    private function separateExtension($filename)
    {
        $dotPos = \mb_strrpos($filename, '.');
        if ($dotPos === false) {
            throw new FileNameException;
        }

        return \mb_substr($filename, $dotPos + 1);
    }

    /**
     * @return string
     */
    private function determineLocation()
    {
        return self::UPLOAD_DIRECTORY . $this->getImageName();
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->id . '.' . $this->extension;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->determineLocation();
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

}