<?php
namespace Happy\Coding\Controller;

/*
 * This file is part of the Happy.Coding package.
 */

use Happy\Coding\Domain\Repository\NewsRepository;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Helper;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Package\Exception\UnknownPackageException;
use Neos\Flow\Package\PackageManager;
use Neos\Neos\Controller\Exception\NodeNotFoundException;
use Neos\Neos\View\FusionView;

class StandardController extends ActionController
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

    //protected $defaultViewObjectName = FusionView::class;

    /**
     * @return void
     */
    public function indexAction()
    {
        $news = $this->newsRepository->findAll();
        $this->view->assignMultiple(
            [
                'news' => $news,
                'identifier' => $this->request->getInternalArgument('__node')->getIdentifier(),
                'node' => $this->request->getInternalArgument('__node')->getNodeType()->getName(),
                'properties' => $this->request->getInternalArgument('__node')->getProperties(),
            ]
        );

    }

    public function helloAction(string $name)
    {
        return 'Hello ' . $name;
    }

    /**
     * @return void
     * @throws StopActionException
     */
    public function redirectAction(): void
    {
        $this->redirect('index');
    }
}
