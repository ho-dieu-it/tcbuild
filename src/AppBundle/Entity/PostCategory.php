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


/**
 * @ORM\Table(name="post_categories") *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostCategoryRepository")
 */
class PostCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;
    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="post_category", cascade={"remove"})
     */
    protected $posts;

    /**
     * @ORM\Column(type="string")
     */
    protected $slug;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;
    /**
     * @ORM\Column(type="string")
     *
     */
    protected $authorEmail;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->posts = new ArrayCollection();
    }
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set name
     *
     * @param string $name
     * @return PostCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Category
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param \DateTime $modifiedAt
     * @return Category
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return \DateTime 
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }
    /**
     * Is the given User the author of this Post?
     *
     * @param User $user
     *
     * @return bool
     */
    public function isAuthor(User $user = null)
    {
        return $user->getEmail() == $this->getAuthorEmail();

    }
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
    }
    public function getSlug()
    {
        return $this->slug;
    }

    public function getType()
    {        
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function addPost(Post $post)
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }

        return $this;
    }

    public function removeJob(Post $post)
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
        }

        return $this;
    }
//    public function __toString()
//    {
//        return $this->getName();
//    }
}
