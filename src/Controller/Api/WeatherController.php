<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class WeatherController extends AbstractController
{
   #[Route('/api/meteo/{ville}', name: 'api_meteo_by_city', methods: ['GET'])]
public function byCity(string $ville, WeatherService $weatherService): JsonResponse
{
    try {
        $weather = $weatherService->getWeatherByCity($ville);

        return $this->json($weather);
    } catch (\RuntimeException $e) {
        $status = str_contains($e->getMessage(), 'Ville non trouvée') ? 404 : 500;

        return $this->json([
            'error' => $e->getMessage()
        ], $status);
    } catch (\Throwable $e) {
        return $this->json([
            'error' => 'Erreur serveur.'
        ], 500);
    }
}

    #[Route('/api/meteo', name: 'api_meteo_current_user_city', methods: ['GET'])]
    public function currentUserCity(
        WeatherService $weatherService,
        UserRepository $userRepository
    ): JsonResponse {
        $securityUser = $this->getUser();

        if (!$securityUser) {
            return $this->json([
                'error' => 'Authentification requise.'
            ], 401);
        }

        $user = $userRepository->findOneBy([
            'email' => $securityUser->getUserIdentifier()
        ]);

        if (!$user) {
            return $this->json([
                'error' => 'Utilisateur non trouvé.'
            ], 404);
        }

        if (!$user->getCityName()) {
            return $this->json([
                'error' => 'Aucune ville renseignée pour cet utilisateur.'
            ], 400);
        }

        try {
            $weather = $weatherService->getWeatherByCity($user->getCityName());

            return $this->json($weather);
        } catch (\RuntimeException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Erreur serveur.'
            ], 500);
        }
    }
}