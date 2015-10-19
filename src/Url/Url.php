<?php

namespace Url;

use App\Exceptions\LogicExceptions\InvalidArgumentException;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="url")
 */
class Url
{
    use Identifier;
    use MagicAccessors;

    /**
     * @ORM\Column(name="url_path", type="string", length=255, nullable=true, unique=true)
     * @var string
     */
    protected $urlPath;

    /**
     * @ORM\Column(name="presenter", type="string", length=255, nullable=true, unique=false)
     * @var string
     */
    private $presenter;

    /**
     * @ORM\Column(name="action", type="string", length=255, nullable=true, unique=false)
     * @var string
     */
    private $action;

    /**
     * @ORM\Column(name="internal_id", type="integer", nullable=true, unique=false, options={"unsigned": false})
     * @var int
     */
    protected $internalId;

    /**
     * @ORM\ManyToOne(targetEntity="Url")
     * @ORM\JoinColumn(name="actual_url", referencedColumnName="id", nullable=true)
     * @var Url
     */
    private $actualUrlToRedirect;

    public function setUrlPath($path)
    {
        Validators::assert($path, 'unicode');
        $this->urlPath = Strings::webalize($path, '/');
    }

    /**
     * @param int|null $internalId
     */
    public function setInternalId($internalId)
    {
        Validators::assert($internalId, 'numericint|null');
        $this->internalId = $internalId;
    }

    /**
     * @param string $presenter
     * @param string|null $action
     */
    public function setDestination($presenter, $action = null)
    {
        if ($action === null) {
            $destination = $presenter;
        } else {
            $destination = $presenter .':'. $action;
        }

        $matches = $this->resolveDestination($destination);

        $this->presenter = $matches[1]; // contains [Module:]Presenter
        // $matches[2] contains Module: if there is a Module
        // $matches[3] contains Presenter
        $this->action = $matches[4]; // action
    }

    private function resolveDestination($destination)
    {
        // ((Module:)*(Presenter)):(action)
        if (!preg_match('~^(([a-zA-z]+:)*([a-zA-z]+)):([a-z]+)$~', $destination, $matches)) {
            throw new InvalidArgumentException('Wrong format of argument $destination. Check if action have lower-case characters.');
        }

        return $matches;
    }

    public function markAsOldUrl($newUrlPathToRedirect)
    {
        $this->actualUrlToRedirect = $newUrlPathToRedirect;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->getPresenter() . ':' . $this->getAction();
    }

    /**
     * @return string
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return Url
     */
    public function getActualUrlToRedirect()
    {
        return $this->actualUrlToRedirect;
    }

}