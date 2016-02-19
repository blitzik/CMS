<?php
/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 19.12.2015
 */

namespace Url\Facades;

use Url\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Url\Url;

class UrlFacade extends Object
{
    /** @var EntityManager */
    private $em;

    /** @var Logger */
    private $logger;


    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('urlsEntities');
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
     * @param string|null $urlPath
     * @return Url|null
     */
    public function getByPath($urlPath)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
           ->from(Url::class, 'u')
           ->where('u.urlPath IS NULL');

        if ($urlPath !== null) {
            $qb->where('u.urlPath = :urlPath')
               ->setParameter('urlPath', $urlPath);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

}