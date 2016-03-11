<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 11.03.2016
 */

namespace Page\Services;

use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Url\Facades\UrlFacade;
use Nette\Object;
use Tags\Tag;

class TagRemover extends Object
{
    public $onSuccessTagRemoval;

    /** @var EntityManager */
    private $em;

    /** @var UrlFacade */
    private $urlFacade;

    /** @var EntityRepository */
    private $tagRepository;


    public function __construct(
        EntityManager $em,
        UrlFacade $urlFacade
    ) {
        $this->em = $em;
        $this->urlFacade = $urlFacade;

        $this->tagRepository = $em->getRepository(Tag::class);
    }


    /**
     * @param $tagID
     * @throws \Exception
     */
    public function remove($tagID)
    {
        try {
            $this->em->beginTransaction();

            /** @var Tag $tag */
            $tag = $this->tagRepository->find($tagID);
            if ($tag === null) {
                $this->em->commit();
                return;
            }

            $tagSearchUrl = $this->urlFacade->getUrl('Pages:Front:Search', 'tag', $tag->getId());

            $this->em->remove($tag);
            $this->em->remove($tagSearchUrl);

            $this->em->flush();
            $this->em->commit();

            $this->onSuccessTagRemoval($tag, $tagID);

        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->close();

            throw $e;
        }
    }

}