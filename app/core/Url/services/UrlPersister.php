<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 12.03.2016
 */

namespace Url\Services;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Url\Url;

class UrlPersister extends Object
{
    /** @var Logger */
    private $logger;

    /** @var EntityManager */
    private $em;


    public function __construct(
        EntityManager $em,
        Logger $logger
    ) {
        $this->em = $em;
        $this->logger = $logger->channel('urlsEntities');
    }


    /**
     * @param Url $url
     * @return Url
     * @throws UrlAlreadyExistsException
     * @throws \Exception
     */
    public function save(Url $url)
    {
        try {
            $this->em->beginTransaction();

            if ($url->getId() !== null) {
                $url = $this->update($url);
            } else {
                $url = $this->create($url);
            }

            $this->em->commit();

        } catch (UrlAlreadyExistsException $uae) {
            $this->closeEntityManager();

            $this->logger->addError(sprintf('Url path already exists: %s', $uae));

            throw $uae;

        } catch (\Exception $e) {
            $this->closeEntityManager();

            $this->logger->addError(sprintf('Url Entity saving failure: %s', $e));

            throw $e;
        }

        return $url;
    }


    /**
     * @param Url $url
     * @return Url
     * @throws UrlAlreadyExistsException
     */
    private function create(Url $url)
    {
        $url = $this->em->safePersist($url);
        if ($url === false) {
            throw new UrlAlreadyExistsException;
        }

        return $url;
    }


    /**
     * @param Url $url
     * @return Url
     * @throws UniqueConstraintViolationException
     */
    private function update(Url $url)
    {
        $this->em->persist($url)->flush();

        return $url;
    }


    private function closeEntityManager()
    {
        $this->em->rollback();
        $this->em->close();
    }
}