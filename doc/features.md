# Features

## Function Name: __construct

**Description:** namespace OpenFoodFacts;  use GuzzleHttp\Client; use GuzzleHttp\ClientInterface; use GuzzleHttp\Exception\GuzzleException; use GuzzleHttp\TransferStats; use OpenFoodFacts\Exception\BadRequestException; use OpenFoodFacts\Exception\ProductNotFoundException; use Psr\Log\LoggerInterface; use Psr\Log\NullLogger; use Psr\SimpleCache\CacheInterface; use Psr\SimpleCache\InvalidArgumentException;  /this class provide [...]It a fork of the python OpenFoodFact rewrite on PHP 7.2@method getIngredients() Collection@method getPurchase_places() Collection@method getPackaging_codes() Collection@method getEntry_dates() Collection/ class Api { /the httpClient for all http request/ private ClientInterface $httpClient;  /this property store the current base of the url/ private string $geoUrl     = 'https://%s.openfoodfacts.org';   /This property store the current location for http callThis property could be world for all product or you can specify le country code (cc) andlanguage of the interface (lc). If you want filter on french product you can set fr as country code.We strongly recommend to use english as language of the interface@example fr-en@link https://en.wiki.openfoodfacts.org/API/Read#Country_code_.28cc.29_and_Language_of_the_interface_.28lc.29@var string/ public string $geography  = 'world';  /this property store the auth parameter (username and password)/ private ?array $auth       = null;  /this property help you to log information/ private LoggerInterface $logger;  private ?CacheInterface $cache;  /this constant defines the environments usable by the API/ private const LIST_API = [ 'food'    => 'https://%s.openfoodfacts.org', 'beauty'  => 'https://%s.openbeautyfacts.org', 'pet'     => 'https://%s.openpetfoodfacts.org', 'product' => 'https://%s.openproductsfacts.org', ];  /This constant defines the facets usable by the APIThis variable is used to create the magic functions like "getIngredients" or "getBrands"/ private const FACETS = [ 'additives', 'allergens', 'brands', 'categories', 'countries', 'contributors', 'code', 'entry_dates', 'ingredients', 'label', 'languages', 'nutrition_grade', 'packaging', 'packaging_codes', 'purchase_places', 'photographer', 'informer', 'states', 'stores', 'traces', ];  /This constant defines the extensions authorized for the downloading of the data@var array/ private const FILE_TYPE_MAP = [ 'mongodb'   => 'openfoodfacts-mongodbdump.tar.gz', 'csv'       => 'en.openfoodfacts.org.products.csv', 'rdf'       => 'en.openfoodfacts.org.products.rdf' ];  /the constructor of the function@param string $currentAPI the environment to search@param string $geography this parameter represent the the country  code and the interface of the language@param LoggerInterface $logger this parameter define an logger@param ClientInterface|null $clientInterface@param CacheInterface|null $cacheInterface

**Function Details:**

```php
public function __construct(
        public readonly string $userAgent,
        private readonly string $currentAPI = 'food',
        string $geography = 'world',
        ?LoggerInterface $logger = null,
        ?ClientInterface $clientInterface = null,
        ?CacheInterface $cacheInterface = null
    ) {
        $this->cache        = $cacheInterface;
        $this->logger       = $logger ?? new NullLogger();
        $this->httpClient   = $clientInterface ?? new Client();

        $this->geoUrl     = sprintf(self::LIST_API[$currentAPI], $geography);
    }
```

<hr>

## Function Name: activeTestMode

**Description:** This function allows you to perform testsThe domain is correct and for testing purposes only

**Function Details:**

```php
public function activeTestMode(): void
    {
        $this->geoUrl = 'https://world.openfoodfacts.net';
        $this->authentification('off', 'off');
    }

    public function getCurrentApi(): string
    {
        return $this->currentAPI;
    }

    /**
     * This function store the authentication parameter
     * @param  string $username
     * @param  string $password
     */
    public function authentification(string $username, string $password): void
    {
        $this->auth = [
            'user_id'   => $username,
            'password'  => $password
        ];
    }

    /**
     * It's a magic function, it works only for facets
     * @param string $name The name of the function
     * @param void $arguments not use yet (probably needed for ingredients)
     * @return Collection        The list of all documents found
     * @throws InvalidArgumentException
     * @throws BadRequestException
     * @example getIngredients()
     */
    public function __call(string $name, $arguments): Collection
    {
        //TODO : test with argument for ingredient
        if (strpos($name, 'get') === 0) {
            $facet = strtolower(substr($name, 3));
            //TODO: what about PSR-12, e.g.: getNutritionGrade() ?

            if (!in_array($facet, self::FACETS)) {
                throw new BadRequestException('Facet "' . $facet . '" not found');
            }
```

<hr>

## Function Name: getProduct

**Description:** this function search an Document by barcode@param string $barcode the barcode [\d]{13}@return Document         A Document if found@throws InvalidArgumentException@throws ProductNotFoundException@throws BadRequestException

**Function Details:**

```php
public function getProduct(string $barcode): Document
    {
        $url = $this->buildUrl('api', 'product', $barcode);

        $rawResult = $this->fetch($url);
        if ($rawResult['status'] === 0) {
            //TODO: maybe return null here? (just throw an exception if something really went wrong?
            throw new ProductNotFoundException('Product not found', 1);
        }
```

<hr>

## Function Name: getByFacets

**Description:** This function return a Collection of Document search by facets@param array $query list of facets with value@param integer $page Number of the page@return Collection     The list of all documents found@throws InvalidArgumentException@throws BadRequestException

**Function Details:**

```php
public function getByFacets(array $query = [], int $page = 1): Collection
    {
        if (empty($query)) {
            return new Collection();
        }
```

<hr>

## Function Name: addNewProduct

**Description:** this function help you to add a new product (or update ??)@param array $postData The post data@return bool|string bool if the product has been added or the error message@throws BadRequestException@throws InvalidArgumentException

**Function Details:**

```php
public function addNewProduct(array $postData)
    {
        if (!isset($postData['code']) || !isset($postData['product_name'])) {
            throw new BadRequestException('code or product_name not found!');
        }
```

<hr>

## Function Name: uploadImage

**Description:** [uploadImage description]@param string $code the barcode of the product@param string $imageField th name of the image@param string $imagePath the path of the image@return array             the http post response (cast in array)@throws BadRequestException@throws InvalidArgumentException

**Function Details:**

```php
public function uploadImage(string $code, string $imageField, string $imagePath)
    {
        //TODO : need test
        if ($this->currentAPI !== 'food') {
            throw new BadRequestException('not Available yet');
        }
```

<hr>

## Function Name: search

**Description:** A search function@param string $search a search term (fulltext)@param integer $page Number of the page@param integer $pageSize The page size@param string $sortBy the sort@return Collection        The list of all documents found@throws BadRequestException@throws InvalidArgumentException

**Function Details:**

```php
public function search(string $search, int $page = 1, int $pageSize = 20, string $sortBy = 'unique_scans')
    {
        $parameters = [
            'search_terms'  => $search,
            'page'          => $page,
            'page_size'     => $pageSize,
            'sort_by'       => $sortBy,
            'json'          => '1',
        ];

        $url = $this->buildUrl('cgi', 'search.pl', $parameters);
        $result = $this->fetch($url, false);

        return new Collection($result, $this->currentAPI);
    }
```

<hr>

## Function Name: downloadData

**Description:** This function download all data from OpenFoodFact@param string $filePath the location where you want to put the stream@param string $fileType mongodb/csv/rdf@return bool             return true when download is complete@throws BadRequestException

**Function Details:**

```php
public function downloadData(string $filePath, string $fileType = 'mongodb')
    {
        if (!isset(self::FILE_TYPE_MAP[$fileType])) {
            $this->logger->warning(
                'OpenFoodFact - fetch - failed - File type not recognized!',
                ['fileType' => $fileType, 'availableTypes' => self::FILE_TYPE_MAP]
            );

            throw new BadRequestException('File type not recognized!');
        }
```

<hr>

## Function Name: fetch

**Description:** This private function do a http request@param string $url the url to fetch@param boolean $isJsonFile the request must be finish by '.json' ?@return array               return the result of the request in array format@throws InvalidArgumentException@throws BadRequestException

**Function Details:**

```php
private function fetch(string $url, bool $isJsonFile = true): array
    {
        $url        .= ($isJsonFile ? '.json' : '');
        $realUrl    = $url;
        $cacheKey   = hash('sha256', $realUrl);

        if (!empty($this->cache) && $this->cache->has($cacheKey)) {
            /** @var array $cachedResult */
            $cachedResult = $this->cache->get($cacheKey);

            return $cachedResult;
        }
```

<hr>

## Function Name: fetchPost

**Description:** $cachedResult = $this->cache->get($cacheKey);  return $cachedResult; } $data = $this->getDefaultOptions();  $data['on_stats'] = function (TransferStats $stats) use (&$realUrl) { // this function help to find redirection // On redirect we lost some parameters like page $realUrl = (string)$stats->getEffectiveUri(); };  try { $response = $this->httpClient->request('get', $url, $data); } catch (GuzzleException $guzzleException) { $this->logger->warning(sprintf('OpenFoodFact - fetch - failed - GET : %s', $url), ['exception' => $guzzleException]); //TODO: What to do on a error? - return empty array? $exception = new BadRequestException($guzzleException->getMessage(), $guzzleException->getCode(), $guzzleException);  throw $exception; } if ($realUrl !== $url) { $this->logger->warning('OpenFoodFact - The url : '. $url . ' has been redirect to ' . $realUrl); } $this->logger->info('OpenFoodFact - fetch - GET : ' . $url . ' - ' . $response->getStatusCode());  /@var array $jsonResult/ $jsonResult = json_decode($response->getBody(), true);  if (!empty($this->cache) && !empty($jsonResult)) { $this->cache->set($cacheKey, $jsonResult); }  return $jsonResult; }  /This function performs the same job of the "fetch" function except the call method and parameters@param string $url The url to fetch@param array $postData The post data@param boolean $isMultipart The data is multipart ?@return array               return the result of the request in array format@throws InvalidArgumentException@throws BadRequestException

**Function Details:**

```php
private function fetchPost(string $url, array $postData, bool $isMultipart = false): array
    {
        $data = $this->getDefaultOptions();

        if ($isMultipart) {
            foreach ($postData as $key => $value) {
                $data['multipart'][] = [
                    'name'      => $key,
                    'contents'  => $value
                ];
            }
```

<hr>

## Function Name: buildUrl

**Description:** This private function generates an url according to the parameters@param  string|null $service@param  string|array|null $resourceType@param  int|string|array|null $parameters@return string               the generated url

**Function Details:**

```php
private function buildUrl(string $service = null, $resourceType = null, $parameters = null): string
    {
        $baseUrl = null;
        switch ($service) {
            case 'api':
                /** @phpstan-ignore-next-line */
                $baseUrl = implode('/', [
                    $this->geoUrl,
                    $service ?? '',
                    'v0',
                    $resourceType,
                    $parameters
                ]);

                break;
            case 'data':
                /** @phpstan-ignore-next-line */
                $baseUrl = implode('/', [
                    $this->geoUrl,
                    $service,
                    $resourceType
                ]);

                break;
            case 'cgi':
                /** @phpstan-ignore-next-line */
                $baseUrl = implode('/', [
                    $this->geoUrl,
                    $service,
                    $resourceType
                ]);
                $baseUrl .= '?' . (is_array($parameters) ? http_build_query($parameters) : $parameters);

                break;
            case null:
            default:
                if (is_array($resourceType)) {
                    $resourceType = implode('/', $resourceType);
                }
```

<hr>

## Function Name: getDefaultOptions

**Description:** $baseUrl = implode('/', [ $this->geoUrl, $service ?? '', 'v0', $resourceType, $parameters ]);  break; case 'data': /@phpstan-ignore-next-line/ $baseUrl = implode('/', [ $this->geoUrl, $service, $resourceType ]);  break; case 'cgi': /@phpstan-ignore-next-line/ $baseUrl = implode('/', [ $this->geoUrl, $service, $resourceType ]); $baseUrl .= '?' . (is_array($parameters) ? http_build_query($parameters) : $parameters);  break; case null: default: if (is_array($resourceType)) { $resourceType = implode('/', $resourceType); } if ($resourceType == 'ingredients') { //need test $resourceType = implode('/', ['state',  'ingredients-completed']); $parameters   = 1; } $baseUrl = implode('/', array_filter([ $this->geoUrl, $resourceType, is_array($parameters) ? '' : $parameters ], function ($value) { return !empty($value); }));  break; }  return $baseUrl; }  /@return array

**Function Details:**

```php
private function getDefaultOptions(): array
    {
        $data = [
            'headers' => $this->getDefaultHeaders(),
        ];
        if ($this->auth) {
            $data['auth'] = array_values($this->auth);
        }
```

<hr>

## Function Name: __construct

**Description:** class Collection implements \Iterator { public const defaultPageSize = 24;  /@var array<int, Document>/ private array $listDocuments  = []; private int $count          = 0; private int$page           = 0; private int$skip           = 0; private int $pageSize       = 0;  /initialization of the collection@param array|null $data the raw data@param string|null $api  this information help to type the collection  (not use yet)

**Function Details:**

```php
public function __construct(?array $data = null, ?string $api = null)
    {
        $data = $data ?? [
            'products'  => [],
            'count'     => 0,
            'page'      => 0,
            'skip'      => 0,
            'page_size' => 0,
        ];
        $this->listDocuments = [];

        if (!empty($data['products'])) {
            $currentApi = '';
            if (null !== $api) {
                $currentApi = $api;
            }
```

<hr>

## Function Name: __construct

**Description:** In mongoDB all element are object, it not possible to define property.All property of the mongodb entity are store in one property of this class and the magic call try to access to it@property string $code@property string $product_name/ class Document { use RecursiveSortingTrait;  /the whole data/ private array $data;  /Initialization the document and specify from which API it was extract@param array $data the whole data

**Function Details:**

```php
public function __construct(array $data)
    {
        $this->recursiveSortArray($data);
        $this->data = $data;
    }
```

<hr>

## Function Name: __get

**Description:** 

**Function Details:**

```php
public function __get(string $name)
    {
        return $this->data[$name];
    }
```

<hr>

## Function Name: __isset

**Description:** 

**Function Details:**

```php
public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Returns a sorted representation of the complete Document Data
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns a Document in the type regarding to the API used.
     * May be a Child of "Document" e.g.: FoodDocument or ProductDocument
     * @param string $apiIdentifier
     * @param array  $data
     * @return Document
     */
    public static function createSpecificDocument(string $apiIdentifier, array $data): Document
    {
        if ($apiIdentifier === '') {
            return new Document($data);
        }
```

<hr>

## Function Name: isAssoc

**Description:** Trait RecursiveSortingTrait/ trait RecursiveSortingTrait { /@param array $arr@return bool

**Function Details:**

```php
private function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Sorts referenced array of arrays in a recursive way for better understandability
     * @param array $arr
     * @see ksort
     * @see asort
     */
    public function recursiveSortArray(array &$arr): void
    {
        if ($this->isAssoc($arr)) {
            ksort($arr);
        }
```

<hr>

## Function Name: __construct

**Description:** the constructor of the function@param string $userAgent this parameter define an user agent@param LoggerInterface $logger this parameter define an logger@param ClientInterface $httpClient@param CacheInterface|null $cacheInterface

**Function Details:**

```php
public function __construct(
        public readonly string $userAgent,
        public readonly LoggerInterface $logger = new NullLogger(),
        public readonly ClientInterface $httpClient = new Client(),
        ?CacheInterface $cacheInterface = null
    ) {
        $this->cache        = $cacheInterface;

    }
```

<hr>

## Function Name: getDocument

**Description:** Function to find a document by him identifier@param string $identifier@param string|null $indexId Index ID to use for the search, if not provided, the default index is used. If there is only one index, this parameter is not needed.@return SearchDocument A Document if found@throws InvalidArgumentException@throws ProductNotFoundException@throws UnknownException@throws ValidationException@throws GuzzleException

**Function Details:**

```php
public function getDocument(string $identifier, string $indexId = null): SearchDocument
    {
        $params = isset($indexId) ? ['index_id' => $indexId] : [];

        $url = "https://search.openfoodfacts.org/document/$identifier?" . http_build_query($params);

        $cacheKey   = hash('sha256', $url);
        if (!empty($this->cache) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }
```

<hr>

## Function Name: search

**Description:** Function to search a list of products@param string|null $query The search query, it supports Lucene search query syntax (https://lucene.apache.org/core/3_6_0/queryparsersyntax.html). Words that are not recognized by the lucene query parser are searched as full text search.Example: categories_tags:"en:beverages" strawberry brands:"casino" query use a filter clause for categories and brands and look for "strawberry" in multiple fields.The query is optional, but sort_by value must then be provided.@param string[] $langs list of languages we want to support during search. This list should include the user expected language, and additional languages (such as english for example).This is currently used for language-specific subfields to choose in which subfields we're searching in.If not provided, ['en'] is used.@param int|null $pageSize Number of results to return per page.@param int|null $page Page to request, starts at 1.@param string[] $fields Fields to include in the response. All other fields will be ignored.@param string|null $sortBy Field name to use to sort results, the field should exist and be sortable. If it is not provided, results are sorted by descending relevance score.@param string|null $indexId Index ID to use for the search, if not provided, the default index is used. If there is only one index, this parameter is not needed.@return SearchResult@throws InvalidParameterException@throws NotFoundException@throws UnknownException@throws ValidationException@throws GuzzleException

**Function Details:**

```php
public function search(string $query = null, array $langs = null, int $pageSize = null, int $page = null, array $fields = null, string $sortBy = null, string $indexId = null): SearchResult
    {
        if(empty($query) && empty($sortBy)) {
            throw new InvalidParameterException('query or sortBy must be provided');
        }
```

<hr>

## Function Name: autocomplete

**Description:** $parameters = []; if(isset($query)) { $parameters['q'] = $query; } if(isset($langs)) { $parameters['langs'] = implode(',', $langs); } if(isset($pageSize)) { $parameters['page_size'] = $pageSize; } if(isset($page)) { $parameters['page'] = $page; } if(isset($fields)) { $parameters['fields'] = implode(',', $fields); } if(isset($sortBy)) { $parameters['sort_by'] = $sortBy; } if(isset($indexId)) { $parameters['index_id'] = $indexId; }  $url = sprintf('https://search.openfoodfacts.org/search?%s', http_build_query($parameters)); $content = $this->request('get', $url);  return new SearchResult($content); }   /Autocomplete function to search for a term in a taxonomy@param string $query User autocomplete query.@param string[] $taxonomyNames Name(s) of the taxonomy to search in.@param string|null $lang Language to search in, default to en.@param int|null $size Number of results to return.@param int|null $fuzziness Fuzziness level to use, default to no fuzziness.@param string|null $indexId Index ID to use for the search, if not provided, the default index is used. If there is only one index, this parameter is not needed.@return AutocompleteResult@throws InvalidParameterException@throws NotFoundException@throws UnknownException@throws ValidationException@throws GuzzleException

**Function Details:**

```php
public function autocomplete(string $query, array $taxonomyNames, string $lang = null, int $size = null, int $fuzziness = null, string $indexId = null): AutocompleteResult
    {
        if(empty($query) || empty($taxonomyNames)) {
            throw new InvalidParameterException('query ans taxonomyNames must be provided');
        }
```

<hr>

## Function Name: request

**Description:** $parameters = [ 'q' => $query, 'taxonomy_names' => implode(',', $taxonomyNames), ]; if(isset($lang)) { $parameters['lang'] = $lang; } if(isset($size)) { $parameters['size'] = $size; } if(isset($fuzziness)) { $parameters['fuzziness'] = $fuzziness; } if(isset($indexId)) { $parameters['index_id'] = $indexId; }  $url = sprintf('https://search.openfoodfacts.org/autocomplete?%s', http_build_query($parameters));  return new AutocompleteResult($this->request('get', $url)); }   /@throws ValidationException@throws NotFoundException@throws UnknownException@throws GuzzleException

**Function Details:**

```php
private function request(string $method, string $url): array
    {
        $response = $this->httpClient->request($method, $url, $this->getDefaultOptions());
        $content = json_decode($response->getBody()->getContents(), true);

        switch ($response->getStatusCode()) {
            case 200:
                return $content;
            case 404:
                throw new NotFoundException();
            case 422:
                $this->logger->error('Validation error', ['http_content' => $content]);

                throw new ValidationException();
            default:
                $this->logger->error('We encounter an unknown http error', ['url' => $url,'http_content' => $content]);

                throw new UnknownException(sprintf('Search return an http error : %s', $response->getStatusCode()));
        }
```

<hr>

## Function Name: Multiply 2 Numbers

**Description:** 

**Function Details:**

```php
private function getDefaultOptions(): array
    {
        return [
            'headers' => [
                'User-Agent' => 'SDK PHP - ' . $this->userAgent,
            ]
        ];
    }

    /**
     * Multiply two numbers
     *
     * @param int|float $a first number
     * @param int|float $b second number
     * @return int|float the result of the multiplication
     */
    function multiply($a, $b) {
        return $a * $b;
    }
```

<hr>

## Function Name: add

**Description:** Add two numbers@param int|float $a first number@param int|float $b second number@return int|float the result of the addition

**Function Details:**

```php
function add($a, $b) {
        return $a + $b;
    }
```

<hr>

## Function Name: __construct

**Description:** public array $listDocuments;  public readonly int $count; /@var bool if false, the value is just an approximation/ public readonly bool $isCountExact; public readonly int $page; public readonly int $pageSize; public readonly int $pageCount; public readonly array $debug; public readonly ?array $warning; /@var int time it took in ms/ public readonly int $took; /@var bool partial content if true ?/ public readonly bool $timedOut; public readonly array $aggregations;  /initialization of the collection@param array $content the raw data

**Function Details:**

```php
public function __construct(array $content)
    {
        $this->count = $content['count'] ?? 0;
        $this->isCountExact = $content['is_count_exact'] ?? false;
        $this->page = $content['page'] ?? 0;
        $this->pageSize = $content['page_size'] ?? 0;
        $this->pageCount = $content['page_count'] ?? 0;
        $this->aggregations = $content['aggregations'] ?? null;

        $this->debug = $content['debug'] ?? [];
        $this->took = $content['took'] ?? 0;
        $this->timedOut = $content['timed_out'] ?? false;
        $this->warning = $content['warnings'] ?? null;

        $this->listDocuments = array_map(
            fn (array $item) => new SearchDocument($item),
            $content['hits'] ?? []
        );
    }
```

<hr>

