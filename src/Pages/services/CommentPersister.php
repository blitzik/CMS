<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 03.03.2016
 */

namespace Pages\Services;

use Comments\Comment;
use Doctrine\ORM\NoResultException;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Pages\Exceptions\Runtime\ActionFailedException;
use Pages\Page;

class CommentPersister extends Object
{
    /** @var EntityManager */
    private $em;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * @param array $values
     * @return Comment
     * @throws ActionFailedException
     */
    public function save(array $values)
    {
        $numberOfComments = $this->getNumberOfComments($values['page']);
        $repliesReferences = $this->findRepliesReferences($values['text']);

        try {
            $this->em->beginTransaction();

            // no replies references found
            if (empty($repliesReferences)) {
                $comment = new Comment($values['author'], $values['text'], $values['page'], $numberOfComments + 1);

                $this->em->persist($comment)->flush();
                $this->em->commit();

                return $comment;
            }

            $commentsToReply = $this->findCommentsToReply($values['page'], $repliesReferences);
            $values['text'] = $this->replaceReplyReferencesByAuthors($values['text'], $commentsToReply);

            $comment = new Comment($values['author'], $values['text'], $values['page'], $numberOfComments + 1);
            $this->em->persist($comment);

            /** @var Comment $comment */
            foreach ($commentsToReply as $commentToReply) {
                $commentToReply->addReaction($comment);
                $this->em->persist($commentToReply);
            }

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->close();

            throw new ActionFailedException;
        }

        return $comment;
    }


    /**
     * @param Page $page
     * @return int
     */
    private function getNumberOfComments(Page $page)
    {
        try {
            return $this->em->createQuery(
                'SELECT COUNT(c.id) AS num FROM ' . Comment::class . ' c
                 WHERE c.page = :page
                 GROUP BY c.page'
            )->setParameter('page', $page)
             ->getSingleScalarResult();

        } catch (NoResultException $e) {
            return 0;
        }
    }


    /**
     * @param string $text
     * @return array
     */
    private function findRepliesReferences($text)
    {
        $regexp = '~^\s*@(?P<orderNumbers>\d+)\s~m';
        $matches = [];
        preg_match_all($regexp, $text, $matches);

        return array_unique($matches['orderNumbers']);
    }


    /**
     * @param Page $page
     * @param array $commentsIDs
     * @return Comment[]
     */
    private function findCommentsToReply(Page $page, array $commentsIDs)
    {
        return $this->em->createQuery(
            'SELECT c FROM ' . Comment::class . ' c INDEX BY c.order
             WHERE c.page = :page AND c.order IN (:orderNumbers)'
        )->execute(['page' => $page, 'orderNumbers' => $commentsIDs]);
    }


    /**
     * @param string$text
     * @param Comment[] $commentsToReply
     * @return string
     */
    private function replaceReplyReferencesByAuthors($text, array $commentsToReply)
    {
        $patterns = [];
        $replacements = [];
        /** @var Comment $comment */
        foreach ($commentsToReply as $comment) {
            $patterns[] = '~^\s*@(' . $comment->getOrder() . ')\s~m';
            $replacements[] = sprintf('
<a href="#comment-%s">#%s. %s</a>, ', $comment->getId(), $comment->getOrder(), $comment->getAuthor());
        }

        return preg_replace($patterns, $replacements, $text);
    }
}