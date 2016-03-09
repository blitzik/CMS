<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Pages\Log\Subscribers;

use Log\Services\AppEventLogger;
use Kdyby\Events\Subscriber;
use Tags\Facades\TagFacade;
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
    )
    {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user->getIdentity();
    }


    function getSubscribedEvents()
    {
        return [
            TagFacade::class . '::onSuccessTagCreation',
            TagFacade::class . '::onSuccessTagEditing',
            TagFacade::class . '::onSuccessTagRemoval'
        ];
    }


    public function onSuccessTagCreation(Tag $tag)
    {
        $this->appEventLogger
            ->saveLog(
                sprintf(
                    'User [%s#%s] has CREATED new Tag [%s#%s]',
                    $this->user->getId(),
                    $this->user->username,
                    $tag->getId(),
                    $tag->name
                ),
                'page_tag',
                'page_tag_creation',
                $this->user->getId()
            );
    }


    public function onSuccessTagEditing(Tag $tag)
    {
        $this->appEventLogger
            ->saveLog(
                sprintf(
                    'User [%s#%s] has UPDATED Tag [%s#%s]',
                    $this->user->getId(),
                    $this->user->username,
                    $tag->getId(),
                    $tag->name
                ),
                'page_tag',
                'page_tag_editing',
                $this->user->getId()
            );
    }


    public function onSuccessTagRemoval(Tag $tag, $id)
    {
        $this->appEventLogger
            ->saveLog(
                sprintf(
                    'User [%s#%s] has REMOVED Tag [%s#%s]',
                    $this->user->getId(),
                    $this->user->username,
                    $id,
                    $tag->name
                ),
                'page_tag',
                'page_tag_removal',
                $this->user->getId()
            );
    }

}