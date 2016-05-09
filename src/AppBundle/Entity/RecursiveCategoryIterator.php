<?php
/**
 * Created by PhpStorm.
 * User: hpdv4
 * Date: 16/09/2015
 * Time: 22:24
 */
// src/AppBundle/Entity/Category.php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


class RecursiveCategoryIterator implements \RecursiveIterator
{
    private $_data;

    public function __construct(Collection $data)
    {
        $this->_data = $data;
    }

    public function hasChildren()
    {
        return ( ! $this->_data->current()->getChildCategories()->isEmpty());
    }

    public function getChildren()
    {
        return new RecursiveCategoryIterator($this->_data->current()->getChildCategories());
    }

    public function current()
    {
        return $this->_data->current();
    }

    public function next()
    {
        $this->_data->next();
    }

    public function key()
    {
        return $this->_data->key();
    }

    public function valid()
    {
        return $this->_data->current() instanceof \Category;
    }

    public function rewind()
    {
        $this->_data->first();
    }
}
