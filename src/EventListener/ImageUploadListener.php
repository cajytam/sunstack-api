<?php

namespace App\EventListener;

use App\Entity\Simulation\FileSimulation;
use App\Entity\Simulation\FileTask;
use App\Entity\Survey\FileSurvey;
use App\Entity\User\User;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;
use Vich\UploaderBundle\Event\Event;

class ImageUploadListener
{
    const HEIGHT_AVATAR = 320;

    const TYPE_MIME_IMAGE = [
        'image/gif',
        'image/png',
        'image/jpeg',
        'image/bmp',
        'image/webp',
    ];

    private string $tmpFile;
    private string $realFile;
    private string $originalName;

    public function __construct(
        private readonly Filesystem $filesystem,
    )
    {
    }

    /**
     * @param Event $event
     * @return void
     */
    public function onVichUploaderPreUpload(Event $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof User) {
            $mapping = $event->getMapping();
            $path = $mapping->getUploadDestination() . '/' . $mapping->getUploadDir($entity);
            if (!$this->filesystem->exists($path)) {
                $this->filesystem->mkdir($path);
            }

            $imagine = new Imagine();
            $tempFile = $event->getObject()->file;
            $tempFilePath = $tempFile->getRealPath();
            $image = $imagine->open($tempFilePath);

            $size = $image->getSize();
            $newWidth = ($size->getWidth() * static::HEIGHT_AVATAR) / $size->getHeight();
            $image->resize(new Box($newWidth, static::HEIGHT_AVATAR));

            $this->tmpFile = $path . 'tmp-' . pathinfo($mapping->getUploadName($entity), PATHINFO_BASENAME);
            $this->realFile = $path . pathinfo($mapping->getUploadName($entity), PATHINFO_BASENAME);

            $image->save($this->tmpFile);

        } elseif ($entity instanceof FileSimulation || $entity instanceof FileSurvey || $entity instanceof FileTask) {
            $this->originalName = $event->getObject()->file->getClientOriginalName();

            $mimeTypes = new MimeTypes();
            $mapping = $event->getMapping();
            $path = $mapping->getUploadDestination() . '/' . $mapping->getUploadDir($entity) . '/';
            if (!$this->filesystem->exists($path)) {
                $this->filesystem->mkdir($path);
            }
            $tempFile = $entity->file;
            $tempFilePath = $tempFile->getRealPath();

            // si le document est une image/*
            if (in_array($mimeTypes->guessMimeType($tempFilePath), static::TYPE_MIME_IMAGE)) {
                $imagine = new Imagine();
                $image = $imagine->open($tempFilePath);
                $this->realFile = $path . pathinfo($mapping->getUploadName($entity), PATHINFO_FILENAME);
                $image->save($this->realFile . '.jpg', array('jpeg_quality' => 80));
            } else {
                $this->realFile = $path . pathinfo($mapping->getUploadName($entity), PATHINFO_FILENAME);
            }
        }
    }

    /**
     * @param Event $event
     * @return void
     * @throws \Exception
     */
    public
    function onVichUploaderPostUpload(Event $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof User) {
            $this->filesystem->remove($this->realFile);
            $this->filesystem->rename(
                $this->tmpFile,
                $this->realFile
            );

            $event->getObject()->setPicture($event->getObject()->getPicture() . '?uid=' . bin2hex(random_bytes(4)));
        } elseif ($entity instanceof FileSimulation || $entity instanceof FileSurvey || $entity instanceof FileTask) {
            $mimeTypes = new MimeTypes();
            $tempFile = $entity->file;
            $tempFilePath = $tempFile->getRealPath();

            if (in_array($mimeTypes->guessMimeType($tempFilePath), static::TYPE_MIME_IMAGE)) {
                $event->getObject()->setFilename(pathinfo($this->realFile, PATHINFO_FILENAME) . '.jpg');
                $this->filesystem->remove($event->getObject()->file->getRealPath());
            }

            // si FileSurvey, on met le nom du fichier en content du survey original
            if ($entity instanceof FileSurvey) {
                $event->getObject()->getSurvey()->setContent($event->getObject()->getFilename());
            } elseif ($entity instanceof FileTask) {
                $entity->setName($this->originalName);
            }
        }
    }
}