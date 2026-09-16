<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AccessDeniedSubscriber implements EventSubscriberInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Intercepte TOUS les accès refusés (Security Kernel ou HTTP 403)
        if ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            
            // Rendu direct de votre template personnalisé Twig
            $content = $this->twig->render('error/error403_custom.html.twig', [
                'custom_message' => "Échec : vous n'avez pas accès à cette ressource.",
            ]);

            // Envoi de la réponse avec un code 403 (Forbidden)
            $response = new Response($content, Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }
    }
}