<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // On ne transforme en JSON que les routes de l'API
        if (
            !str_starts_with($request->getPathInfo(), '/api')
            && !str_starts_with($request->getPathInfo(), '/auth')
        ) {
            return;
        }

        $exception = $event->getThrowable();

        $statusCode = 500;
        $message = 'Erreur serveur.';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();

            $message = match ($statusCode) {
                400 => 'Requête invalide.',
                401 => 'Authentification requise.',
                403 => 'Accès refusé.',
                404 => 'Ressource non trouvée.',
                default => $exception->getMessage() ?: 'Erreur HTTP.'
            };
        }

        $response = new JsonResponse([
            'code' => $statusCode,
            'message' => $message,
        ], $statusCode);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onExceptionEvent',
        ];
    }
}