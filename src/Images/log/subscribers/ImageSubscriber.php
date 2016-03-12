<?php

/**
 * Created by PhpStorm.
 * Author: Aleš Tichava
 * Date: 09.03.2016
 */

namespace Images\Log\Subscribers;

use Log\Services\AppEventLogger;
use Images\Facades\ImageFacade;
use Kdyby\Events\Subscriber;
use Nette\Security\User;
use Nette\Object;
use Images\Image;

class ImageSubscriber extends Object implements Subscriber
{
    /** @var AppEventLogger */
    private $appEventLogger;

    /** @var \Users\User */
    private $user;

    /** @var string */
    private $fileRoot;

    public function __construct(
        AppEventLogger $appEventLogger,
        User $user
    ) {
        $this->appEventLogger = $appEventLogger;
        $this->user = $user->getIdentity();
    }


    public function setImageFileRoot($fileRoot)
    {
        $this->fileRoot = $fileRoot;
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


    public function onSuccessImageUpload(Image $image) // todo zkouknout zda-li se odkaz tvori spravne
    {
        $this->appEventLogger
             ->saveLog(
                 sprintf(
                     'User [%s#%s] <b>has UPLOADED</b> the %sImage [%s#%s]%s',
                     $this->user->getId(),
                     $this->user->username,
                     (isset($this->fileRoot) ? sprintf('<a href="%s/%s">', $this->fileRoot, $image->getComposedFilePath()) : ''),
                     $image->getId(),
                     $image->getOriginalName(),
                     (isset($this->fileRoot) ? '</a>' : '')
                 ),
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
                     'User [%s#%s] <b>has REMOVED</b> the Image [%s#%s]',
                     $this->user->getId(),
                     $this->user->username,
                     $imageID,
                     $imageOriginalName
                 ),
                 'image_removal',
                 $this->user->getId()
             );
    }

}