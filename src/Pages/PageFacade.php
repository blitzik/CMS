<?php

namespace Pages\Facades;

use App\Exceptions\Runtime\ArticleTitleAlreadyExistsException;
use App\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Monolog\Logger;
use Nette\Object;
use Nette\Utils\Strings;
use Pages\Article;
use Url\Url;

class PageFacade extends Object
{
    /** @var EntityManager  */
    private $em;

    /** @var Logger  */
    private $logger;

    /** @var  EntityRepository */
    private $articleRepository;

    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('pages');
        $this->articleRepository = $this->em->getRepository(Article::class);
    }

    /**
     * @param Article $article
     * @return array ['article' => Article, 'url' => Url]
     * @throws ArticleTitleAlreadyExistsException
     * @throws UrlAlreadyExistsException
     */
    public function save(Article $article)
    {
        try {
            $this->em->beginTransaction();

            $article = $this->em->safePersist($article);
            if ($article === false) {
                throw new ArticleTitleAlreadyExistsException;
            }

            $articleUrl = $this->establishArticleUrl($article);
            $articleUrl = $this->em->safePersist($articleUrl);
            if ($articleUrl === false) {
                throw new UrlAlreadyExistsException;
            }

            $this->em->commit();
            return ['article' => $article, 'url' => $articleUrl];

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('Article saving error');
        }
    }

    /**
     * @param Article $article
     * @return Url
     */
    private function establishArticleUrl(Article $article)
    {
        $url = new Url;
        $url->setUrlPath(Strings::webalize($article->title));
        $url->setDestination(Article::PRESENTER, Article::PRESENTER_ACTION);
        $url->setInternalId($article->getId());

        return $url;
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @return mixed|null|object
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->articleRepository->findOneBy($criteria, $orderBy);
    }
}