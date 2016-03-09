<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Images\Log\Subscribers;

use Images\Facades\ImageFacade;
use Images\Image;
use Kdyby\Events\Subscriber;
use Log\Services\AppEventLogger;
use Nette\Object;
use Nette\Security\User;

class ImageSubscriber extends Object implements Subscriber
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

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            ImageFacade::class . '::onSuccessImageUpload',
            ImageFacade::class . '::onSuccessImageRemoval'
        ];
    }


    public function onSuccessImageUpload(Image $image)
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has UPLOADED the Image [%s#%s]',
                     $this->user->getId(),
                     $this->user->username,
                     $image->getId(),
                     $image->getOriginalName()
                 ),
                 'image',
                 'image_upload',
                 $this->user->getId()
             );
    }


    public function onSuccessImageRemoval($imageName)
    {
        $rearSlashPosition = mb_strpos($imageName, '/');
        $imageID = mb_substr($imageName, 0, $rearSlashPosition);
        $imageOriginalName = mb_substr($imageName, $rearSlashPosition + 1);

        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] has REMOVED the Image [%s#%s]',
                     $this->user->getId(),
                     $this->user->username,
                     $imageID,
                     $imageOriginalName
                 ),
                 'image',
                 'image_removal',
                 $this->user->getId()
             );
    }

}