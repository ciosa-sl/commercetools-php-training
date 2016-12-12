<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\TrainingBundle\Service;

use Commercetools\Core\Client;
use Commercetools\Core\Error\ResourceNotFoundError;
use Commercetools\Core\Model\Common\LocalizedString;
use Commercetools\Core\Model\Type\FieldDefinition;
use Commercetools\Core\Model\Type\FieldDefinitionCollection;
use Commercetools\Core\Model\Type\StringType;
use Commercetools\Core\Model\Type\Type;
use Commercetools\Core\Model\Type\TypeDraft;
use Commercetools\Core\Request\Types\TypeByKeyGetRequest;
use Commercetools\Core\Request\Types\TypeCreateRequest;

class TypeRepository
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Type
     */
    public function getCheckReserveType()
    {
        $request = TypeByKeyGetRequest::ofKey('CheckReserve');
        $response = $this->client->execute($request);

        if ($response->isError()) {
            if ($response->getErrors()->getByCode(ResourceNotFoundError::CODE)) {
                $draft = TypeDraft::ofKeyNameDescriptionAndResourceTypes(
                    'CheckReserve',
                    LocalizedString::ofLangAndText('en', 'CheckReserve'),
                    LocalizedString::ofLangAndText('en', 'CheckReserve'),
                    ['order']
                );
                $draft->setFieldDefinitions(
                    FieldDefinitionCollection::of()->add(
                        FieldDefinition::of()
                            ->setType(StringType::of())
                            ->setName('note')
                            ->setLabel(LocalizedString::ofLangAndText('en', 'Note'))
                            ->setRequired(false)
                            ->setInputHint('SingleLine')
                    )
                );
                $type = $this->createType($draft);
            } else {
                throw new \Exception('something happened');
            }
        } else {
            $type = $request->mapFromResponse($response);
        }
        return $type;
    }

    /**
     * @param TypeDraft $type
     * @return Type
     */
    public function createType(TypeDraft $type)
    {
        $request = TypeCreateRequest::ofDraft($type);
        $response = $this->client->execute($request);
        $type = $request->mapFromResponse($response);
        return $type;
    }
}
