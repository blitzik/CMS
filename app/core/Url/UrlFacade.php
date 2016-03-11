<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 19.12.2015
 */

namespace Url\Facades;

use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Doctrine\DBAL\DBALException;
use Url\Exceptions\Runtime\UrlNotPersistedException;
use Url\Services\UrlLinker;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Url\Url;

class UrlFacade extends Object
{
    /** @var EntityRepository */
    private $urlRepository;

    /** @var UrlLinker */
    private $urlLinker;

    /** @var Logger */
    private $logger;

    /** @var EntityManager */
    private $em;


    public function __construct(
        EntityManager $entityManager,
        UrlLinker $urlLinker,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('urlsEntities');
        $this->urlLinker = $urlLinker;

        $this->urlRepository = $this->em->getRepository(Url::class);
    }


    /**
     * @param Url $url
     * @return Url
     * @throws UrlAlreadyExistsException
     * @throws DBALException
     */
    public function saveUrl(Url $url)
    {
        try {
            $this->em->beginTransaction();

            $u = $this->em->safePersist($url);
            if ($u === false) { // already exists
                throw new UrlAlreadyExistsException;
            }

            $this->em->commit();

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError(sprintf('Url Entity saving failure: %s', $e));

            throw $e;
        }

        return $u;
    }


    /**
     * @param Url $old
     * @param Url $new
     * @return void
     */
    public function linkUrls(Url $old, Url $new)
    {
        $this->urlLinker->linkUrls($old, $new);
    }


    /**
     * @param string $urlPath
     * @return Url|null
     */
    public function getByPath($urlPath)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
           ->from(Url::class, 'u')
           ->where('u.urlPath = :urlPath')
           ->setParameter('urlPath', $urlPath);

        return $qb->getQuery()->getOneOrNullResult();
    }


    /**
     * @param int $urlId
     * @return Url|null
     */
    public function getById($urlId)
    {
        return $this->urlRepository->find($urlId);
    }


    /**
     * @param string $presenter
     * @param string $action
     * @param int $internal_id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUrl($presenter, $action, $internal_id)
    {
        return $this->em->createQuery(
            'SELECT u FROM ' . Url::class . ' u
             WHERE u.presenter = :presenter AND u.action = :action AND u.internalId = :internalID'
        )->setParameters([
            'presenter' => $presenter,
            'action' => $action,
            'internalID' => $internal_id
        ])->getOneOrNullResult();
    }

}