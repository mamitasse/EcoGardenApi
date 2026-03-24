<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly string $openWeatherApiKey
    ) {
    }

    public function getWeatherByCity(string $city): array
    {
        $city = trim($city);

        if ($city === '') {
            throw new \InvalidArgumentException('La ville ne peut pas être vide.');
        }

        $cacheKey = 'weather_' . md5(mb_strtolower($city));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city) {

            // ⏱️ Cache pendant 1 heure
            $item->expiresAfter(3600);

            $response = $this->httpClient->request(
                'GET',
                'https://api.openweathermap.org/data/2.5/weather',
                [
                    'query' => [
                        'q' => $city,
                        'appid' => $this->openWeatherApiKey,
                        'units' => 'metric',
                        'lang' => 'fr',
                    ],
                ]
            );

            $statusCode = $response->getStatusCode();

            // ❌ Ville inexistante
            if ($statusCode === 404) {
                throw new \RuntimeException('Ville non trouvée.');
            }

            // ❌ Autres erreurs API (clé invalide, quota, etc.)
            if ($statusCode >= 400) {
                $errorContent = $response->getContent(false);

                throw new \RuntimeException(
                    'Erreur OpenWeather (' . $statusCode . ') : ' . $errorContent
                );
            }

            // ✅ Récupération des données
            $data = $response->toArray(false);

            return [
                'city' => $data['name'] ?? $city,
                'temperature' => $data['main']['temp'] ?? null,
                'description' => $data['weather'][0]['description'] ?? null,
                'humidity' => $data['main']['humidity'] ?? null,
                'wind_speed' => $data['wind']['speed'] ?? null,
            ];
        });
    }
}