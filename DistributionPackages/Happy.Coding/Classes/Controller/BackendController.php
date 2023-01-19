<?php
namespace Happy\Coding\Controller;

/*
 * This file is part of the Happy.Coding package.
 */

use Happy\Coding\Domain\Model\News;
use Happy\Coding\Domain\Repository\NewsRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Helper;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Package\Exception\UnknownPackageException;
use Neos\Flow\Package\PackageManager;

class BackendController extends ActionController
{
    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var NewsRepository
     * @Flow\Inject
     */
    protected NewsRepository $newsRepository;

    /**
     * @return void
     */
    public function indexAction()
    {
        $news = $this->newsRepository->findAll();
        $this->view->assign(
            'news', $news
        );
    }

    public function newAction(): void
    {
    }

    public function createAction(News $news): void
    {
        $this->newsRepository->add($news);
        $this->addFlashMessage('Created a new $news.');
        $this->redirect('index');
    }

    public function editAction(News $news): void
    {
        $this->view->assign('news', $news);
        $identifier = $this->persistenceManager->getIdentifierByObject($news);
        $this->view->assign('identifier', $identifier);
    }

    public function updateAction()
    {
        $arguments = $this->request->getArguments();
        $identifier = $arguments['news']['identifier'];
        $title = $arguments['news']['title'];
        $description = $arguments['news']['description'];
        $news = $this->newsRepository->findByIdentifier($identifier);
        $news->setTitle($title);
        $news->setDescription($description);

        $this->newsRepository->update($news);
        $this->addFlashMessage('Updated the $news.');
        $this->redirect('index');
    }

    public function deleteAction(News $news): void
    {
        $this->newsRepository->remove($news);
        $this->persistenceManager->persistAll();
        $this->addFlashMessage('Deleted a $news.');
        $this->redirect('index');
    }
}
