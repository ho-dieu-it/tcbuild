<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageRepository")
 * @ORM\Table(name="Pages")
 */
class Page
{
    /**
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See http://symfony.com/doc/current/best_practices/configuration.html#constants-vs-configuration-options
     */
    const NUM_ITEMS = 10;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="menu")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id", nullable=false, onDelete="SET NULL")
     */
    private $menu;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $slug;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="page.blank_summary")
     */
    private $summary;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min = "10", minMessage = "page.too_short_content")
     */
    private $content;

    /**
     * @ORM\Column(type="string")
     * @Assert\Email()
     */
    private $authorEmail;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(
     *      targetEntity="Image",
     *      mappedBy="page",
     *      cascade={"persist"}
     * )
     * @ORM\OrderBy({"createdAt"="DESC"})
     *
     */
    private  $files;

    /**
     * @var ArrayCollection
     */
    private $uploadedFiles;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->images = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
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

    public function getCreatedAt()
    {
        return $this->publishedAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    public function getMenu()
    {
        return $this->menu;
    }
    public function setMenu(Menu $menu = null)
    {
        $this->menu = $menu;
    }   

    public function getFiles() {
        return $this->files;
    }
    public function setFiles(array $files) {
        $this->files = $files;
    }
    /**
     * @return ArrayCollection
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }
    /**
     * @param ArrayCollection $uploadedFiles
     */
    public function setUploadedFiles($uploadedFiles)
    {
        $this->uploadedFiles = $uploadedFiles;
    }
    /**
     * @ORM\PreFlush()
     */
    public function upload($uploadDir)
    {
        try {
            foreach ($this->uploadedFiles as $uploadedFile) {
                if ($uploadedFile) {
                    $file = new Image();

                    /*
                     * These lines could be moved to the File Class constructor to factorize
                     * the File initialization and thus allow other classes to own Files
                     */

                    $path = sha1(uniqid(mt_rand(), true)) . '.' . $uploadedFile->guessExtension();
                    $file->setPath($path);
                    $file->setName($uploadedFile->getClientOriginalName());
                    $file->setType($uploadedFile->guessExtension());
                    $file->setSize($uploadedFile->getClientsize());
                    $uploadedFile->move($uploadDir, $path);

                    $this->getFiles()->add($file);
                    $file->setPage($this);
                    unset($uploadedFile);
                }
            }
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
}
