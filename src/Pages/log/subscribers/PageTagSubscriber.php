<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Pages\Log\Subscribers;

use Log\Services\AppEventLogger;
use Page\Services\TagPersister;
use Page\Services\TagRemover;
use Kdyby\Events\Subscriber;
use Nette\Security\User;
use Nette\Object;
use Tags\Tag;

class PageTagSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;

    /** @var \Users\User */
    private $user;


    public function __construct(
        AppEventLogger $appEventLogger,
        User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user->getIdentity();
    }


    function getSubscribedEvents()
    {
        return [
            TagPersister::class . '::onSuccessTagCreation',
            TagPersister::class . '::onSuccessTagEditing',
            TagRemover::class . '::onSuccessTagRemoval'
        ];
    }


    public function onSuccessTagCreation(Tag $tag)
    {
        $this->appEventLogger
            ->saveLog(
                sprintf(
                    'User [%s#%s] <b>has CREATED</b> new Tag [%s#%s]',
                    $this->user->getId(),
                    $this->user->username,
                    $tag->getId(),
                    $tag->getName()
                ),
                'page_tag_creation',
                $this->user->getId()
            );
    }


    public function onSuccessTagEditing(Tag $tag)
    {
        $this->appEventLogger
            ->saveLog(
                sprintf(
                    'User [%s#%s] <b>has UPDATED</b> Tag [%s#%s]',
                    $this->user->getId(),
                    $this->user->username,
                    $tag->getId(),
                    $tag->getName()
                ),
                'page_tag_editing',
                $this->user->getId()
            );
    }


    public function onSuccessTagRemoval(Tag $tag, $id)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has REMOVED</b> Tag [%s#%s]',
                     $this->user->getId(),
                     $this->user->username,
                     $id,
                     $tag->getName()
                 ),
                 'page_tag_removal',
                 $this->user->getId()
             );
    }

}