<?php
namespace Happy\Coding\Domain\Model;

/*
 * This file is part of the Happy.Coding package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Media\Domain\Model\ImageInterface;

/**
 * @Flow\Entity
 */
class News
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

//    /**
//     * @var ImageInterface
//     */
//    protected $image = null;


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    /**
     * @return ImageInterface
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param ImageInterface $image
     * @return void
     */
    public function setImage(ImageInterface $image = null)
    {
        if (!is_null($image)) {
            $this->image = $image;
        }
    }
}
