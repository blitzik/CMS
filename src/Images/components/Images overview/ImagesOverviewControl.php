<?php

namespace Images\Components;

use App\Components\BaseControl;
use App\Exceptions\Runtime\FileRemovalException;
use blitzik\IPaginatorFactory;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\AbstractQuery;
use Images\Facades\ImageFacade;
use Images\Query\ImageQuery;
use Nette\Application\UI\ITemplate;
use Nette\Utils\Paginator;

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
        try {
            $this->imageFacade->removeImage($imageName);
            $this->flashMessage('Obrázek byl úspěšně odstraněn', 'success');

        } catch (FileRemovalException $fr) {
            $this->flashMessage('Při pokusu o odstranění obrázku [ '.$imageName.' ] došlo k chybě', 'error');
        } catch (DBALException $e) {
            $this->flashMessage('Při pokusu o odstranění obrázku [ '.$imageName.' ] došlo k chybě', 'error');
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