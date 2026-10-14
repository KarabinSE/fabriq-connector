<?php

namespace Karabin\FabriqConnector;

use Karabin\FabriqConnector\Requests\ContactIndexRequest;
use Karabin\FabriqConnector\Requests\MenuShowRequest;
use Karabin\FabriqConnector\Requests\NewsIndexRequest;
use Karabin\FabriqConnector\Requests\NewsShowRequest;
use Karabin\FabriqConnector\Requests\PageShowRequest;
use Karabin\FabriqConnector\Requests\PreviewShowRequest;
use Saloon\Http\Response;

class FabriqRepository
{
    protected readonly FabriqConnector $connector;

    public function __construct($locale)
    {
        $locale ??= app()->getLocale();
        $this->connector = new FabriqConnector($locale);
    }

    public function page(string $slug = 'start')
    {
        $request = request('preview', false)
            ? new PreviewShowRequest(request()->input('preview'))
            : new PageShowRequest($slug);

        $response = $this->connector->send($request);

        if ($response->failed()) {
            abort($response->status(), $response->json('message') ?? '');
        }

        return (object) [
            'page' => $response->json(),
            'blocks' => $response->json('data.content.data.boxes', []),
        ];
    }

    /**
     * Fetches fabriq menus and maps them to the given keys
     *
     * @param  array<string, string>  $menus
     * @return array
     */
    public function menus(array $menus)
    {
        $data = [];

        $requests = collect($menus)->flatMap(function ($fabriqMenuName, $menuName) {
            return [$menuName => new MenuShowRequest($fabriqMenuName, ['include' => 'children,content,children.content'])];
        });

        $this->connector->pool()
            ->setRequests($requests->toArray())
            ->setConcurrency(5)
            ->withResponseHandler(function (Response $response, string $key) use (&$data) {
                $data[$key] = $response->json('data');
            })
            ->withExceptionHandler(function ($reason, string $key) use (&$data) {
                \Log::info('Failed to fetch menu with key.', ['key' => $key, 'reason' => $reason]);
                $data[$key] = [];
            })
            ->send()
            ->wait();

        return $data;
    }

    public function contacts(array $params)
    {
        $request = new ContactIndexRequest($params);

        return $this->connector->send($request);
    }

    public function articles(?array $params = [])
    {
        $request = new NewsIndexRequest([
            'include' => 'content,slugs',
            ...$params,
        ]);

        return $this->connector->send($request);
    }

    public function article(string $slug, $params = [])
    {
        $request = new NewsShowRequest($slug, [
            'include' => 'content,slugs',
            ...$params,
        ]);

        return $this->connector->send($request);
    }
}
