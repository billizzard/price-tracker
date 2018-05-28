<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $entityId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted = false;

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?int
    {
        return $this->userId;
    }

    public function setUser(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function setType(Uploadable $entity): self
    {
        $this->type = $entity->getEntityType();

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setEntity(Uploadable $entity): self
    {
        $this->entityId = $entity->getId();
        $this->setType($entity);

        return $this;
    }

    public function getEntity(): ?int
    {
        return $this->entityId;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $dir = $this->getFullDir();
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new FileNotFoundException('Directory "' . $dir . '" not found');
            }
        }
        $file->move($this->getFullDir(), $fileName);

        return $fileName;
    }

    public function getDir(): string
    {
        return '/uploads/' . $this->getType() . '/';
    }

    public function getFullDir(): string
    {
        return __DIR__ . '/../../public' . $this->getDir();
    }

    public function getSrc(): string
    {
        return $this->getDir() . $this->getName();
    }

    public function getFullSrc(): string
    {
        return $this->getFullDir() . $this->getName();
    }

    public function delete()
    {
        $this->isDeleted = true;
    }
}
