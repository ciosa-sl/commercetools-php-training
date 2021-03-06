<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\TrainingBundle\Tests\Service;


use Commercetools\Core\Model\CustomObject\CustomObject;
use Commercetools\Core\Model\CustomObject\CustomObjectDraft;
use Commercetools\Core\Request\CustomObjects\CustomObjectByKeyGetRequest;
use Commercetools\Core\Request\CustomObjects\CustomObjectCreateRequest;
use Commercetools\TrainingBundle\Tests\TrainingTestCase;

class CustomObjectRepositoryTest extends TrainingTestCase
{
    public function testCreate()
    {
        $object = ['key' => 'value'];

        $repository = $this->container->get('commercetools_training.service.custom_object_repository');

        $key = 'test'. time();

        $customObject = $repository->store('test', $key, $object);

        $this->assertInstanceOf(CustomObject::class, $customObject);
        $this->assertNotNull($customObject->getId());
        $this->assertSame('test', $customObject->getContainer());
        $this->assertSame($key, $customObject->getKey());
        $this->assertSame($object, $customObject->getValue());
    }

    public function testNewCustomerNumber()
    {
        $repository = $this->container->get('commercetools_training.service.custom_object_repository');

        $customerNumberOld = $repository->getNewCustomerNumber();
        $customerNumber = $repository->getNewCustomerNumber();

        $this->assertSame($customerNumberOld + 1, $customerNumber);
    }

    public function testCustomerNumberConcurrency()
    {
        $repository = $this->container->get('commercetools_training.service.custom_object_repository');

        $customerNumberOld = $repository->getNewCustomerNumber();

        $customerNumberObject = $repository->getCustomerNumberObject();
        $response = $repository->store(
            $customerNumberObject->getContainer(),
            $customerNumberObject->getKey(),
            ($customerNumberObject->getValue() + 1),
            $customerNumberObject->getVersion()
        );
        $this->assertSame($customerNumberObject->getId(), $response->getId());

        $customerNumber = $repository->getNewCustomerNumber($customerNumberObject);
        $this->assertSame($customerNumberOld + 2, $customerNumber);
    }
}
