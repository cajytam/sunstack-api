<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PatchMultipartFormDataListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->getMethod() !== Request::METHOD_PATCH
            || !str_starts_with($request->headers->get('Content-Type'), 'multipart/form-data')
        ) {
            return;
        }

        $content = $request->getContent();

        $boundary = substr($request->headers->get('Content-Type'), 30);
        $parts = array_slice(explode('--' . $boundary, $content), 1, -1);

        $data = [];
        $files = [];

        foreach ($parts as $part) {
            if (str_contains($part, 'filename=')) {
                preg_match('/filename="([^"]*)"/', $part, $matches);
                preg_match('/name="([^"]*)"/', $part, $fileMatches);
                $fileFieldName = $fileMatches[1];
                $filename = $matches[1];
                list($header, $body) = explode("\r\n\r\n", $part, 2);
                $body = substr($body, 0, -2);

                $tempPath = tempnam(sys_get_temp_dir(), 'upload');
                file_put_contents($tempPath, $body);
                $file = new UploadedFile(
                    $tempPath,
                    $filename,
                    null,
                    null,
                    true
                );

                $files[$fileFieldName] = $file;
            } else {
                list($header, $body) = explode("\r\n\r\n", $part, 2);
                preg_match('/name="([^"]*)"/', $header, $matches);
                $fieldName = $matches[1];
                $body = substr($body, 0, -2);

                $data[$fieldName] = $body;
            }
        }

        $request->files->add($files);
        $request->request->replace($data);
    }
}