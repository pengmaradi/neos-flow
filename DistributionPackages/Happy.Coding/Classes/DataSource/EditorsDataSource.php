<?php

namespace Happy\Coding\DataSource;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class EditorsDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    static protected $identifier = 'happy-coding-editors';

    /**
     * @var UserService
     * @Flow\Inject
     */
    protected UserService $userService;

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected PersistenceManagerInterface $persistenceManager;

    public function getData(NodeInterface $node = null, array $arguments = [])
    {
        $options = [];
        foreach ($this->userService->getUsers() as $user) {
            $options[$this->persistenceManager->getIdentifierByObject($user)] = ['label' => $user->getLabel()];
        }
        return $options;
    }
}