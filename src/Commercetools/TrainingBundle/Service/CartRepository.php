<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\TrainingBundle\Service;

use Commercetools\Core\Client;
use Commercetools\Core\Model\Cart\Cart;
use Commercetools\Core\Model\Cart\CartDraft;
use Commercetools\Core\Model\Common\Address;
use Commercetools\Core\Model\CustomField\CustomFieldObject;
use Commercetools\Core\Model\CustomField\CustomFieldObjectDraft;
use Commercetools\Core\Request\Carts\CartByIdGetRequest;
use Commercetools\Core\Request\Carts\CartCreateRequest;
use Commercetools\Core\Request\Carts\CartUpdateRequest;
use Commercetools\Core\Request\Carts\Command\CartAddLineItemAction;
use Commercetools\Core\Request\Carts\Command\CartChangeLineItemQuantityAction;
use Commercetools\Core\Request\Carts\Command\CartRemoveLineItemAction;

class CartRepository
{
    private $client;
    private $types;

    public function __construct(Client $client, TypeRepository $types)
    {
        $this->client = $client;
        $this->types = $types;
    }

    /**
     * @param CartDraft $cartDraft
     * @return Cart
     */
    public function createCart(CartDraft $cartDraft)
    {
        $request = CartCreateRequest::ofDraft($cartDraft);
        $response = $this->client->execute($request);
        $cart = $request->mapFromResponse($response);
        return $cart;
    }

    /**
     * @param Cart $cart
     * @param $productId
     * @param $variantId
     * @param $quantity
     * @return Cart
     */
    public function addLineItem(Cart $cart, $productId, $variantId, $quantity)
    {
        $request = CartUpdateRequest::ofIdAndVersion($cart->getId(), $cart->getVersion());
        $request->addAction(CartAddLineItemAction::ofProductIdVariantIdAndQuantity($productId, $variantId, $quantity));

        $response = $this->client->execute($request);
        $cart = $request->mapFromResponse($response);
        return $cart;
    }

    /**
     * @param Cart $cart
     * @param $lineItemId
     * @param $quantity
     * @return Cart
     */
    public function changeLineItemQuantity(Cart $cart, $lineItemId, $quantity)
    {
        $request = CartUpdateRequest::ofIdAndVersion($cart->getId(), $cart->getVersion());
        $request->addAction(CartChangeLineItemQuantityAction::ofLineItemIdAndQuantity($lineItemId, $quantity));

        $response = $this->client->execute($request);
        $cart = $request->mapFromResponse($response);
        return $cart;
    }

    /**
     * @param Cart $cart
     * @param $lineItemId
     * @return Cart
     */
    public function removeLineItem(Cart $cart, $lineItemId)
    {
        $request = CartUpdateRequest::ofIdAndVersion($cart->getId(), $cart->getVersion());
        $request->addAction(CartRemoveLineItemAction::ofLineItemId($lineItemId));

        $response = $this->client->execute($request);
        $cart = $request->mapFromResponse($response);
        return $cart;
    }

    /**
     * @param $cartId
     * @return Cart
     */
    public function getCart($cartId)
    {
        $request = CartByIdGetRequest::ofId($cartId);
        $response = $this->client->execute($request);
        $cart = $request->mapFromResponse($response);
        return $cart;
    }

    /**
     * @param null $cartId
     * @return Cart
     */
    public function getOrCreateCart($cartId = null)
    {
        if (is_null($cartId)) {
            $cartDraft = CartDraft::ofCurrency('EUR')
                ->setShippingAddress(Address::of()->setCountry('DE'))
                ->setCustom(CustomFieldObjectDraft::ofTypeKey('CheckReserve'));
            ;
            $cart = $this->createCart($cartDraft);
        } else {
            $cart = $this->getCart($cartId);
        }
        if (is_null($cart)) {
            $cart = Cart::of();
        }

        return $cart;
    }
}
