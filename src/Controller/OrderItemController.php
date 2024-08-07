<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\OrderItem;
use App\Service\OrderPricesRecalculator;
use Sylius\Bundle\OrderBundle\Controller\OrderItemController as OrderItemControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderItemController extends OrderItemControllerBase
{

    public function getCart(){

        $cart = $this->getContext()->getCart();
        $items = $cart->getItems();

        $data = [
            'item_count'=>$cart->getTotalQuantity(),
            'total'=>$cart->getTotal(),
            'items'=>[]
        ];

        /** @var OrderItem[] $items */
        foreach ($items as $item){

            $product = $item->getProduct();

            $data['items'][] = [
                'id'=>$item->getId(),
                'quantity'=>$item->getQuantity(),
                'price'=>$item->getUnitPrice(),
                'quotation'=>$product->getRequire_quotation(),
                'in_stock'=>$item->getVariant()->isInStock(),
                'product_title'=>$item->getProductName(),
                'sku'=>$item->getVariant()->getCode()
            ];
        }

        return $this->json($data);
    }

    public function clearCart(){

        $cart = $this->getContext()->getCart();
        $cart->clearItems();

        $cartManager = $this->getCartManager();
        $cartManager->persist($cart);
        $cartManager->flush();

        return $this->json([]);
    }

    public function refreshCart(OrderPricesRecalculator $orderPricesRecalculator){

        $cart = $this->getContext()->getCart();

        $orderPricesRecalculator->process($cart);

        $cartManager = $this->getCartManager();
        $cartManager->persist($cart);
        $cartManager->flush();

        return $this->getCart();
    }

    public function updateCart(Request $request){
        $cart = $this->getContext()->getCart();

        $data = json_decode($request->getContent(), true);

        if (!isset($data['line']) || !isset($data['quantity'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $itemId = (int)$data['line'];
        $quantity = (int)$data['quantity'];

        $orderItemQuantityModifier = $this->container->get('sylius.order_item_quantity_modifier');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $items = $cart->getItems();

        if (!$items[$itemId]) {
            throw new NotFoundHttpException('Cart item not found.');
        }

        $orderItemQuantityModifier->modify($items[$itemId], $quantity);
        $entityManager->flush();

        return $this->getCart();
    }
}
