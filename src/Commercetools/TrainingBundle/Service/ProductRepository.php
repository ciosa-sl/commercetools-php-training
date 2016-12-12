<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\TrainingBundle\Service;

use Commercetools\Core\Client;
use Commercetools\Core\Model\Product\ProductProjection;
use Commercetools\Core\Model\Product\ProductProjectionCollection;
use Commercetools\Core\Request\Products\ProductProjectionByIdGetRequest;
use Commercetools\Core\Request\Products\ProductProjectionSearchRequest;
use Commercetools\Core\Response\PagedSearchResponse;
use Commercetools\Symfony\CtpBundle\Model\Search;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\HttpFoundation\Request;

class ProductRepository
{
    private $client;
    private $searchModel;

    public function __construct(Client $client, Search $searchModel)
    {
        $this->client = $client;
        $this->searchModel = $searchModel;
    }

    /**
     * @param Request $request
     * @return PagedSearchResponse
     */
    public function getProducts(Request $request = null)
    {
        $searchRequest = ProductProjectionSearchRequest::of();
        if (!is_null($request)) {
            $uri = new Uri($request->getRequestUri());
            $searchRequest = $this->getSearchRequest($searchRequest, $uri);
        }
        $response = $this->client->execute($searchRequest);

        return $response;
    }


    /**
     * @param string $productId
     * @return ProductProjection
     */
    public function getProductById($productId)
    {
        $request = ProductProjectionByIdGetRequest::ofId($productId);
        $response = $this->client->execute($request);
        $product = $request->mapFromResponse($response);
        return $product;
    }

    /**
     * @param ProductProjectionSearchRequest $request
     * @param Uri $uri
     * @return ProductProjectionSearchRequest
     */
    public function getSearchRequest(ProductProjectionSearchRequest $request, Uri $uri)
    {
        $searchValues = [];
        if (!is_null($request)) {
            $searchValues = $this->searchModel->getSelectedValues($uri);
        }

        return $this->searchModel->addFacets($request, $searchValues);
    }
}
