<?php
namespace Happy\Coding\Domain\Repository;

/*
 * This file is part of the Happy.Coding package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class NewsRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => \Neos\Flow\Persistence\QueryInterface::ORDER_DESCENDING
    ];
    // add customized methods here
}
