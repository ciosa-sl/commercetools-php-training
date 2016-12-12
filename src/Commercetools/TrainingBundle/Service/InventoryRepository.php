<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\TrainingBundle\Service;

use Commercetools\Core\Client;
use Commercetools\Core\Model\Inventory\InventoryDraft;
use Commercetools\Core\Model\Inventory\InventoryEntry;
use Commercetools\Core\Request\Inventory\InventoryCreateRequest;
use Commercetools\Core\Request\Inventory\InventoryQueryRequest;

class InventoryRepository
{
    private $client;
    private $channels;

    public function __construct(Client $client, ChannelRepository $channels)
    {
        $this->client = $client;
        $this->channels = $channels;
    }

    /**
     * @param $sku
     * @return InventoryEntry
     */
    public function getInventory($sku)
    {
        $request = InventoryQueryRequest::of()->where(sprintf('sku = "%s"', $sku));
        $response = $this->client->execute($request);
        $inventories = $request->mapFromResponse($response);
        $inventory = $inventories->current();
        return $inventory;
    }

    /**
     * @param InventoryDraft $inventoryDraft
     * @return InventoryEntry
     */
    public function createInventory(InventoryDraft $inventoryDraft)
    {
        $request = InventoryCreateRequest::ofDraft($inventoryDraft);
        $response = $this->client->execute($request);
        $inventory = $request->mapFromResponse($response);
        return $inventory;
    }
}
