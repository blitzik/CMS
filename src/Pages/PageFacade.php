<?php

namespace Pages\Facades;

use App\Exceptions\LogicExceptions\DateTimeFormatException;
use App\Exceptions\LogicExceptions\InvalidArgumentException;
use App\Exceptions\Runtime\ArticlePublicationException;
use App\Exceptions\Runtime\ArticleTitleAlreadyExistsException;
use App\Exceptions\Runtime\UrlAlreadyExistsException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\AbstractQuery;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Monolog\Logger;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Object;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use Pages\Article;
use Pages\Query\ArticleQuery;
use Tags\Tag;
use Url\Url;

class PageFacade extends Object
{
    /** @var EntityManager  */
    private $em;

    /** @var Logger  */
    private $logger;

    /** @var  EntityRepository */
    private $articleRepository;

    /** @var  Cache */
    private $cache;

    public function __construct(
        EntityManager $entityManager,
        IStorage $storage,
        Logger $logger
    ) {
        $this->em = $entityManager;
        $this->logger = $logger->channel('pages');
        $this->cache = new Cache($storage, 'articles');

        $this->articleRepository = $this->em->getRepository(Article::class);
    }

    /**
     * @param Article $article
     * @param array $tags
     * @return Article
     * @throws ArticleTitleAlreadyExistsException
     * @throws UrlAlreadyExistsException
     * @throws ArticlePublicationException
     * @throws DateTimeFormatException
     */
    public function save(Article $article, $values, array $tags)
    {
        $values['tags'] = $tags; // values from Form [key => tagId]

        if ($values['time'] == null and $values['isPublished'] == true) {
            throw new ArticlePublicationException;
        }

        try {
            $this->em->beginTransaction();

            if ($article->getId() === null) {
                $article = $this->createNewArticle($article, $values);
            } else {
                $article = $this->updateArticle($article, $values);
            }

            $this->em->flush();
            $this->em->commit();

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('Article saving error:'. $e->getMessage());
        }

        return $article;
    }

    /**
     * @param Article $article
     * @param $values
     * @return Article
     * @throws \Exception
     * @throws ArticleTitleAlreadyExistsException
     * @throws ArticlePublicationException
     * @throws UrlAlreadyExistsException
     * @throws DateTimeFormatException
     */
    private function createNewArticle(Article $article, $values)
    {
        $article->setPublishedAt($values['time']);
        $article->setArticleVisibility($values['isPublished']);

        /** @var Article $article */
        $article = $this->em->safePersist($article);
        if ($article === false) {
            throw new ArticleTitleAlreadyExistsException;
        }

        $articleUrl = $this->establishArticleUrl($article);
        $articleUrl = $this->em->safePersist($articleUrl);
        if ($articleUrl === false) {
            throw new UrlAlreadyExistsException;
        }

        $this->addTags2Article($article, $values['tags']);

        $this->em->persist($article);

        return $article;
    }

    /**
     * @param Article $article
     * @param $values
     * @return Article
     * @throws DateTimeFormatException
     */
    private function updateArticle(Article $article, $values)
    {
        $article->setTitle($values['title']);
        $article->setIntro($values['intro']);
        $article->setText($values['text']);
        $article->setPublishedAt($values['time']);
        $article->setArticleVisibility($values['isPublished']);

        $article->clearTags();

        $this->addTags2Article($article, $values['tags']);

        $this->em->persist($article);

        return $article;
    }

    private function addTags2Article(Article $article, array $tags)
    {
        foreach ($tags as $tagId) {
            /** @var Tag $tag */
            $tag = $this->em->getReference(Tag::class, $tagId);
            $article->addTag($tag);
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
     * @param ArticleQuery $query
     * @return array|\Kdyby\Doctrine\ResultSet
     */
    public function fetchArticles(ArticleQuery $query)
    {
        return $this->articleRepository->fetch($query);
    }

    /**
     * @param $articleId
     * @return Article|null
     */
    public function getArticle($articleId)
    {
        return $this->getBaseArticleDql()
                    ->where('a.id = :id')
                    ->setParameter('id', $articleId)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
    
    /**
     * @param $articleId
     * @return array|null
     */
    public function getArticleAsArray($articleId)
    {
        $article = $this->getBaseArticleDql()
                        ->where('a.id = :id AND a.isPublished = true AND a.publishedAt <= CURRENT_TIMESTAMP()')
                        ->setParameter('id', $articleId)
                        ->getQuery()
                        ->getArrayResult();

        if (empty($article)) {
            return null;
        }

        return $article[0];
    }

    public function publishArticle($id)
    {
        $this->em->createQuery(
            'UPDATE ' .Article::class. ' a SET a.isPublished = true
             WHERE a.id = :id'
        )->execute(['id' => $id]);
    }

    public function hideArticle($id)
    {
        $this->em->createQuery(
            'UPDATE ' .Article::class. ' a SET a.isPublished = false
             WHERE a.id = :id'
        )->execute(['id' => $id]);
    }

    /**
     * @param Article $article
     * @throws DBALException
     */
    public function removeArticle(Article $article)
    {
        try {
            $this->em->beginTransaction();

            $url_path = Strings::webalize($article->title);

            /** @var Url $url */
            $url = $this->em->createQuery(
                'SELECT u FROM ' . Url::class . ' u
                 WHERE u.urlPath = :url_path'
            )->setParameter('url_path', $url_path)
             ->getOneOrNullResult();

            if ($url !== null) {
                $this->cache->clean([Cache::TAGS => $url->getCacheKey()]);
                $this->em->remove($url);
                $this->em->remove($article);
                $this->em->flush();
            }

            $this->em->commit();

        } catch (DBALException $e) {
            $this->em->rollback();
            $this->em->close();

            $this->logger->addError('article removal error'); // todo err msg

            throw $e;
        }
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    private function getBaseArticleDql()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('a, t')
           ->from(Article::class, 'a')
           ->leftJoin('a.tags', 't');

        return $qb;
    }
}