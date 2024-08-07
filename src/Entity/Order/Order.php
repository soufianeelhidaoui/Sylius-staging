<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder
{

    public const STATE_COMPLETED= 'completed';
    public const STATE_CONFIRMED = 'confirmed';

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    public $service_center_id;

    public function getItem_count(){

        return $this->countItems();
    }

    public function getTotal_price(){

        return $this->getTotal();
    }

    /**
     * @return mixed
     */
    public function getServiceCenterId()
    {
        return $this->service_center_id;
    }

    /**
     * @param mixed $service_center_id
     */
    public function setServiceCenterId($service_center_id): void
    {
        $this->service_center_id = $service_center_id;
    }
}
