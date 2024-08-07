<?php

/*
 * This file is part of Monsieur Biz' Menu plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;

class OrderRepository extends BaseOrderRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(Order::class));
    }

    public function getStats($service_center_id, $state){

        $qb = $this->createQueryBuilder('o');

        $qb->select('SUM(o.total) as total')
            ->addSelect('COUNT(o.id) as count')
            ->where('o.state = :state')
            ->andWhere('o.paymentState = :paymentState')
            ->andWhere('o.service_center_id = :service_center_id')
            ->setParameter('state', $state)
            ->setParameter('paymentState', 'paid')
            ->setParameter('service_center_id', $service_center_id);

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * @param Order $order
     * @return array
     */
    public function hydrate(Order $order){

        $customer = $order->getCustomer();
        $address = $order->getBillingAddress();

        $data = [
            "id" => $order->getId(),
            "number" => $order->getNumber(),
            "serviceCenterId" => $order->getServiceCenterId(),
            "createdAt" => $order->getCreatedAt()->format('d/m/Y'),
            "state" => $order->getState(),
            "total" => $order->getTotal(),
            "countItems" => $order->countItems(),
            "notes" => $order->getNotes(),
            "items" => [],
            "customer" => [
                "firstName" => $address->getFirstName(),
                "lastName" => $address->getLastName(),
                "phoneNumber" => $address->getPhoneNumber(),
                "email" => $customer->getEmail(),
                "street" => $address->getStreet(),
                "postcode" => $address->getPostcode(),
                "city" => $address->getCity(),
                "countryCode" => $address->getCountryCode()
            ]
        ];

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item){

            $product = $item->getProduct();
            $variant = $item->getVariant();

            $data["items"][] = [
                [
                    "id" => $item->getId(),
                    "name" => $product->getName(),
                    "code" => $product->getCode(),
                    "quantity" => $item->getQuantity(),
                    "total" => $item->getTotal(),
                    "image" => $item->getImage(),
                    "variant"=>[
                        "id"=>$variant->getId(),
                        "code"=>$variant->getCode()
                    ]
                ]
            ];
        }

        return $data;
    }
}
