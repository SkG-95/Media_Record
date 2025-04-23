<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TmdbApiService
{
    private $httpClient;
    private $params;
    private $apiKey;
    private $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->apiKey = $this->params->get('app.tmdb_api_key');
    }

    public function searchMedia(string $query, string $type = 'multi', int $page = 1)
    {
        return $this->makeRequest("/search/{$type}", [
            'query' => $query,
            'page' => $page
        ]);
    }

    public function getMediaDetails(int $id, string $type)
    {
        return $this->makeRequest("/{$type}/{$id}");
    }

    public function getPopularMedia(string $type = 'movie', int $page = 1)
    {
        return $this->makeRequest("/{$type}/popular", [
            'page' => $page
        ]);
    }

    public function getTopRatedMedia(string $type = 'movie', int $page = 1)
    {
        return $this->makeRequest("/{$type}/top_rated", [
            'page' => $page
        ]);
    }

    public function getGenres(string $type = 'movie')
    {
        return $this->makeRequest("/genre/{$type}/list");
    }

    private function makeRequest(string $endpoint, array $params = [])
    {
        $params['api_key'] = $this->apiKey;
        $params['language'] = 'fr-FR';

        $response = $this->httpClient->request('GET', $this->baseUrl . $endpoint, [
            'query' => $params
        ]);

        return $response->toArray();
    }
}
