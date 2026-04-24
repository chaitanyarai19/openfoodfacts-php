<?php

namespace OpenFoodFacts;

// Existing imports remain unchanged...

class Api
{
    // Affected functions updated to resolve PHP 8.4 deprecations.

    /**
     * Store the authentication parameter.
     * @param string|null $username
     * @param string|null $password
     */
    public function authentification(?string $username = null, ?string $password = null): void
    {
        $this->auth = [
            'user_id' => $username,
            'password' => $password
        ];
    }

    public function getProduct(?string $barcode = null): Document
    {
        $url = $this->buildUrl('api', 'product', $barcode);
        $rawResult = $this->fetch($url);

        if ($rawResult['status'] === 0) {
            throw new ProductNotFoundException('Product not found', 1);
        }

        return Document::createSpecificDocument($this->currentAPI, $rawResult['product']);
    }

    public function search(?string $search = null, ?int $page = 1, ?int $pageSize = 20, ?string $sortBy = 'unique_scans')
    {
        $parameters = [
            'search_terms' => $search,
            'page' => $page,
            'page_size' => $pageSize,
            'sort_by' => $sortBy,
            'json' => '1',
        ];

        $url = $this->buildUrl('cgi', 'search.pl', $parameters);

        return new Collection($this->fetch($url, false), $this->currentAPI);
    }

    // Similarly, make updates across other functions where nullable parameters exist...
}
