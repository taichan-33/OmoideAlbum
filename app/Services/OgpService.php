<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class OgpService
{
    /**
     * Fetch OGP data from a URL.
     *
     * @param string $url
     * @return array
     */
    public function fetch(string $url): array
    {
        try {
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                return $this->defaultData($url);
            }

            $html = $response->body();

            // Suppress warnings for malformed HTML
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            $xpath = new DOMXPath($doc);

            $data = [
                'title' => $this->getMetaContent($xpath, 'og:title') ?? $this->getTitle($doc),
                'description' => $this->getMetaContent($xpath, 'og:description') ?? $this->getMetaNameContent($xpath, 'description'),
                'image_url' => $this->getMetaContent($xpath, 'og:image'),
                'site_name' => $this->getMetaContent($xpath, 'og:site_name'),
                'url' => $this->getMetaContent($xpath, 'og:url') ?? $url,
            ];

            return array_filter($data);  // Remove null values
        } catch (\Exception $e) {
            return $this->defaultData($url);
        }
    }

    private function getMetaContent(DOMXPath $xpath, string $property): ?string
    {
        $nodes = $xpath->query("//meta[@property='{$property}']/@content");
        return $nodes->length > 0 ? $nodes->item(0)->nodeValue : null;
    }

    private function getMetaNameContent(DOMXPath $xpath, string $name): ?string
    {
        $nodes = $xpath->query("//meta[@name='{$name}']/@content");
        return $nodes->length > 0 ? $nodes->item(0)->nodeValue : null;
    }

    private function getTitle(DOMDocument $doc): ?string
    {
        $nodes = $doc->getElementsByTagName('title');
        return $nodes->length > 0 ? $nodes->item(0)->nodeValue : null;
    }

    private function defaultData(string $url): array
    {
        return [
            'url' => $url,
            'title' => $url,  // Fallback to URL as title
        ];
    }
}
