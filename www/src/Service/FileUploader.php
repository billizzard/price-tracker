<?php
namespace App\Service;

use App\Entity\Uploadable;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(Uploadable $model, UploadedFile $uploadedFile, User $user)
    {
        $file = new \App\Entity\File();
        $file->setEntity($model);
        $file->setName($file->upload($uploadedFile));
        $file->setUser($user->getId());

        return $file;
    }
}