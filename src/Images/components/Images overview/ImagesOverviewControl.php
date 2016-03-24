<?php

namespace Images\Components;

use blitzik\FlashMessages\FlashMessage;
use Images\Exceptions\Runtime\FileRemovalException;
use Nette\Application\UI\ITemplate;
use Doctrine\DBAL\DBALException;
use App\Components\BaseControl;
use Doctrine\ORM\AbstractQuery;
use Images\Facades\ImageFacade;
use blitzik\IPaginatorFactory;
use Images\Query\ImageQuery;
use Nette\Utils\Paginator;
use Users\Authorization\Permission;

class ImagesOverviewControl extends BaseControl
{
    /** @var IPaginatorFactory  */
    private $paginatorFactory;

    /** @var ImageFacade  */
    private $imageFacade;

    /** @var ImageQuery */
    private $imageQuery;



    public function __construct(
        ImageQuery $imageQuery,
        ImageFacade $imageFacade,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->imageFacade = $imageFacade;
        $this->paginatorFactory = $paginatorFactory;
        $this->imageQuery = $imageQuery;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/imagesOverview.latte');

        $imagesResultSet = $this->imageFacade
                                ->fetchImages($this->imageQuery);

        /** @var Paginator $paginator */
        $paginator = $this['vs']->getPaginator();

        $imagesResultSet->applyPaginator($paginator, 15);

        $template->images = $imagesResultSet->toArray(AbstractQuery::HYDRATE_ARRAY);

        $template->render();
    }


    /**
     * @return ITemplate
     */
    protected function createTemplate()
    {
        $template = parent::createTemplate();

        $template->addFilter('formatSizeUnits', function ($size) {
            if ($size >= 1024) {
                return floor($size / 1024) . 'KB';
            }

            return $size . 'B';
        });

        return $template;
    }


    protected function createComponentVs()
    {
        $comp = $this->paginatorFactory->create();
        $comp->onPaginate[] = function () {
            $this->redrawControl();
        };

        return $comp;
    }


    public function handleImageRemove($imageName)
    {
        if (!$this->user->isAllowed('image', Permission::ACL_REMOVE)) {
            $this->flashMessage('authorization.noPermission', FlashMessage::WARNING);
            $this->redirect('this');
        }

        try {
            $this->imageFacade->removeImage($imageName);
            $this->flashMessage('images.overview.actions.remove.messages.success', FlashMessage::SUCCESS);

        } catch (FileRemovalException $fr) {
            $this->flashMessage('images.overview.actions.remove.messages.removalError', FlashMessage::ERROR, ['name' => $imageName]);
        } catch (DBALException $e) {
            $this->flashMessage('images.overview.actions.remove.messages.removalError', FlashMessage::ERROR, ['name' => $imageName]);
        }

        $this->redirect('this');
    }
}


interface IImagesOverviewControlFactory
{
    /**
     * @param ImageQuery $imageQuery
     * @return ImagesOverviewControl
     */
    public function create(ImageQuery $imageQuery);
}