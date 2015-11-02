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
 * @ORM\Table(name="image")
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
        $this->originalName = $file->getSanitizedName();
        $this->uploadedAt = new \DateTime('now');
    }

    /**
     * Returns extension including . (dot) e.g. [.jpg]
     *
     * @param $filename
     * @return string
     */
    private function getExtension($filename)
    {
        $dotPos = strrpos($filename, '.');
        if ($dotPos === false) {
            throw new FileNameException;
        }

        return substr($filename, $dotPos);
    }

    /**
     * @param int $id
     * @param string $filename
     * @return string
     */
    private function determineLocation($id, $filename)
    {
        return self::UPLOAD_DIRECTORY . $id . $this->getExtension($filename);
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->determineLocation($this->id, $this->originalName);
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @return \DateTime
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }

}