<?php

/*
 * Propstack PHP API
 *
 * Copyright (c) 2021 pdir / digital agentur // pdir GmbH
 *
 * @package    propstack-api
 * @link       https://github.com/pdir/propstack-api
 * @license    MIT
 * @author     Mathias Arzberger <develop@pdir.de>
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\Propstack;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Pdir\Propstack\Exceptions\PropstackApiException;
use Psr\Http\Message\ResponseInterface;

/**
 * Client for the Propstack API v1.
 *
 * @see https://docs.propstack.de/
 */
class Api
{
    /**
     * Default page size for paginated requests (Propstack recommends max. 500).
     */
    const DEFAULT_PER_PAGE = 100;

    /**
     * Safety limit to prevent endless loops when paginating.
     */
    const MAX_PAGES = 1000;

    /**
     * the propstack api url.
     *
     * @var string
     */
    private $api = 'https://api.propstack.de';

    /**
     * the propstack api version.
     *
     * @var string
     */
    protected $apiVersion = 'v1';

    /**
     * the propstack api key.
     *
     * @var string
     */
    private $apiKey;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(array $credentials = [], ?ClientInterface $client = null)
    {
        $this->apiKey = (string) ($credentials['apiKey'] ?? getenv('PROPSTACK_API_KEY') ?: '');
        $this->client = $client ?? $this->createClient();
    }

    /**
     * @throws PropstackApiException
     */
    public function getProjects(array $query = []): ?array
    {
        return $this->requestGet('projects', $query);
    }

    /**
     * Returns one page of saved queries (Suchprofile).
     *
     * Supported query parameters: client, page, per.
     *
     * @throws PropstackApiException
     */
    public function getSavedQueries(array $query = []): ?array
    {
        return $this->requestGet('saved_queries', $query);
    }

    /**
     * Returns all saved queries (Suchprofile) by walking through all pages.
     *
     * The result has the same shape as a single page: ['data' => [...]].
     * Additionally meta.total_count contains the number of loaded saved queries.
     *
     * @throws PropstackApiException
     */
    public function getAllSavedQueries(array $query = [], int $perPage = self::DEFAULT_PER_PAGE): array
    {
        $items = [];
        $page = 1;

        do {
            $result = $this->getSavedQueries(array_merge($query, ['page' => $page, 'per' => $perPage]));
            $data = $result['data'] ?? [];
            $items = array_merge($items, $data);
            ++$page;
        } while (\count($data) >= $perPage && $page <= self::MAX_PAGES);

        return [
            'data' => $items,
            'meta' => ['total_count' => \count($items)],
        ];
    }

    /**
     * Returns a single contact.
     *
     * Supported query parameters: include (children, documents, relationships, owned_properties).
     *
     * @throws PropstackApiException
     */
    public function getContact(int $id, array $query = []): ?array
    {
        return $this->requestGet('contacts/'.$id, $query);
    }

    /**
     * @deprecated use getContact()
     *
     * @throws PropstackApiException
     */
    public function getContacts($id, array $query = []): ?array
    {
        return $this->getContact((int) $id, $query);
    }

    /**
     * @throws PropstackApiException
     */
    public function request(string $method, string $resource, array $options = []): ?ResponseInterface
    {
        if ('' === $this->apiKey) {
            throw new PropstackApiException('No Propstack API key configured. Pass "apiKey" or set PROPSTACK_API_KEY.');
        }

        // The key is sent as header so it does not end up in URLs, logs or proxies
        $options[RequestOptions::HEADERS] = array_merge(
            $options[RequestOptions::HEADERS] ?? [],
            ['X-API-KEY' => $this->apiKey]
        );

        try {
            $response = $this->client->request(strtoupper($method), $this->createApiUrl($resource), $options);
        } catch (GuzzleException $e) {
            $message = 'Propstack API request failed';

            if (401 === $e->getCode()) {
                $errJson = json_decode((string) strstr($e->getMessage(), '{'));
                $message = $errJson->errors[0] ?? 'Unauthorized';
            }

            throw new PropstackApiException($message, $e->getCode(), $e);
        }

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        return $response;
    }

    /**
     * @throws PropstackApiException
     */
    private function requestGet(string $resource, array $query = []): ?array
    {
        $response = $this->request('get', $resource, $query ? [RequestOptions::QUERY => $query] : []);

        if (null === $response) {
            return null;
        }

        return $this->getArrayFromJsonBody($response);
    }

    /**
     * @throws PropstackApiException
     */
    private function getArrayFromJsonBody(ResponseInterface $response): array
    {
        $data = json_decode((string) $response->getBody(), true);

        if (!\is_array($data)) {
            throw new PropstackApiException('Invalid JSON response from Propstack API.');
        }

        return $data;
    }

    protected function createApiUrl(string $resource = ''): string
    {
        return $this->api.'/'.$this->apiVersion.'/'.ltrim($resource, '/');
    }

    private function createClient(): Client
    {
        return new Client([
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
            RequestOptions::TIMEOUT => 30,
        ]);
    }
}
