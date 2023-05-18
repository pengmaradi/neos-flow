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
use const Neos\Flow\var_dump;

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
        if ($this->request->getInternalArgument('__node')) {
            $node = $this->request->getInternalArgument('__node');
            $this->view->assignMultiple(
                [
                    'news' => $news,
                    'identifier' => $node->getIdentifier(),
                    'node' => $node->getNodeType()->getName(),
                    'properties' => $node->getProperties(),
                ]
            );
        } else {
            $this->view->assignMultiple(
                [
                    'news' => $news,
                ]
            );
        }
    }

    public function helloAction(string $name = 'neos route testing...')
    {
        $this->view->assignMultiple(
            [
                'name' => $name,
            ]
        );
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
