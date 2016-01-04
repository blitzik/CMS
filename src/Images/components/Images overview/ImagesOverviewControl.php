<?php

namespace Images\Components;

use App\Components\BaseControl;
use App\Exceptions\Runtime\FileRemovalException;
use blitzik\IPaginatorFactory;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\AbstractQuery;
use Images\Facades\ImageFacade;
use Images\Query\ImageQuery;
use Nette\Utils\Paginator;

class ImagesOverviewControl extends BaseControl
{
    /** @var IPaginatorFactory  */
    private $paginatorFactory;

    /** @var ImageFacade  */
    private $imageFacade;


    public function __construct(
        ImageFacade $imageFacade,
        IPaginatorFactory $paginatorFactory
    ) {
        $this->imageFacade = $imageFacade;
        $this->paginatorFactory = $paginatorFactory;
    }


    public function render()
    {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . '/imagesOverview.latte');

        $imagesResultSet = $this->imageFacade
                                ->fetchImages(
                                    (new ImageQuery())
                                );

        /** @var Paginator $paginator */
        $paginator = $this['vs']->getPaginator();

        $imagesResultSet->applyPaginator($paginator, 10);

        $template->images = $imagesResultSet->toArray(AbstractQuery::HYDRATE_ARRAY);

        $template->render();
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
     * @return ImagesOverviewControl
     */
    public function create();
}