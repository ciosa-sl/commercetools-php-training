<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\TrainingBundle\Service;

use Commercetools\Core\Client;
use Commercetools\Core\Error\ConcurrentModificationError;
use Commercetools\Core\Model\CustomObject\CustomObject;
use Commercetools\Core\Model\CustomObject\CustomObjectDraft;
use Commercetools\Core\Request\CustomObjects\CustomObjectByKeyGetRequest;
use Commercetools\Core\Request\CustomObjects\CustomObjectCreateRequest;

class CustomObjectRepository
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $value
     * @return CustomObject
     */
    public function store($container, $key, $value, $version = null)
    {
        $object = CustomObjectDraft::ofContainerKeyAndValue($container, $key, $value);
        if (!is_null($version)) {
            $object->setVersion($version);
        }
        $request = CustomObjectCreateRequest::ofObject($object);
        $response = $this->client->execute($request);
        return $request->mapFromResponse($response);
    }

    /**
     * @return CustomObject
     */
    public function getCustomerNumberObject()
    {
        $request = CustomObjectByKeyGetRequest::ofContainerAndKey('stuff', 'customerNumber');
        $response = $this->client->execute($request);
        $customObject = $request->mapFromResponse($response);

        if (is_null($customObject)) {
            $customObject = CustomObjectDraft::ofContainerKeyAndValue('stuff', 'customerNumber', 0);
        }
        return $customObject;
    }

    public function getNewCustomerNumber($customObject = null)
    {
        if (is_null($customObject)) {
            $customObject = $this->getCustomerNumberObject();
        }

        $newCustomerNumber = $customObject->getValue() + 1;
        $customObject->setValue($newCustomerNumber);

        $request = CustomObjectCreateRequest::ofObject($customObject);
        $response = $this->sendObject($request, $customObject);

        $retries = 0;
        while ($retries < 5 && $response->isError()) {
            $error = $response->getErrors()->getByCode(ConcurrentModificationError::CODE);
            if ($error instanceof ConcurrentModificationError) {
                $customObject = $this->getCustomerNumberObject();
                $newCustomerNumber = $customObject->getValue() + 1;
                $customObject->setValue($newCustomerNumber);
                usleep(10000);
                $response = $this->sendObject($request, $customObject);
            } else {
                throw new \Exception('something happened');
            }
            $retries++;
        }
        $customObject = $request->mapFromResponse($response);

        return $customObject->getValue();
    }

    public function sendObject(CustomObjectCreateRequest $request, $customObject)
    {
        $request = $request->setObject($customObject);
        return $this->client->execute($request);
    }
}
