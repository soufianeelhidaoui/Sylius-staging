<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order_item")
 */
class OrderItem extends BaseOrderItem
{
    public function getImage(){

        $product = $this->getProduct();
        $images = $product->getImagesByType('main');

        if( !$images->count() )
            $images = $product->getImages();

        if( $images->count() )
            return $images->first()->getPath();

        return false;
    }

    public function getPrice(){
        return $this->getDiscountedUnitPrice();
    }

    public function getLine_price(){
        return $this->getSubtotal();
    }

    public function getOriginal_price(){
        return $this->getUnitPrice();
    }
}
