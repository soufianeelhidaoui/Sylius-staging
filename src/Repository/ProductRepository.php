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

use App\Entity\Channel\Channel;
use App\Entity\Product\Product;
use App\Entity\Product\ProductTaxon;
use App\Entity\Product\ProductTranslation;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;
use Sylius\Component\Core\Model\Image;

class ProductRepository extends BaseProductRepository
{
    public function hydrate(Product $_product){

        /** @var ProductTranslation[] $translations */
        $translations = $_product->getTranslations();

        $channels = $_product->getChannels();
        $productTaxons = $_product->getProductTaxons();

        /** @var Image[] $images */
        $images = $_product->getImages();

        $product = [
            'id' => $_product->getId(),
            'code' => $_product->getCode(),
            'available' => $_product->getAvailable(),
	        'createdAt' => $_product->getCreatedAt(),
	        'updatedAt' => $_product->getUpdatedAt(),
	        'syncedAt' => $_product->getSyncedAt(),
	        'quantity' => $_product->getTotal_quantity(),
            'channels' => [],
            'images' => [],
            'translations' => [],
            'taxons' => []
        ];

        foreach ($translations as $translation){

            $product['translations'][$translation->getLocale()] = [
                'name' => $translation->getName(),
                'slug' => $translation->getSlug(),
                'description' => $translation->getDescription()
            ];
        }

        foreach ($images as $image){

            $product['images'][] = [
	            'path' => $image->getPath(),
	            'type' => $image->getType(),
	            'updatedAt' => $image->getUpdatedAt(),
	            'syncedAt' => $image->getSyncedAt()
            ];
        }

        /** @var Channel[] $channels */
        foreach ($channels as $channel){

            $product['channels'][] = [
                'name' => $channel->getName(),
                'code' => $channel->getCode()
            ];
        }

        /** @var ProductTaxon[] $productTaxons */
        foreach ($productTaxons as $productTaxon){

            $product['taxons'][] = [
                'name' => $productTaxon->getTaxon()->getName(),
                'code' => $productTaxon->getTaxon()->getCode()
            ];
        }

        return $product;
    }

    /**
     * @param $syncedAt
     * @return Product[]
     */
    public function findDivergentes($syncedAt){

        $query = $this->createQueryBuilder('p');

        $query->where('p.synced_at < :syncedAt')
            ->setParameter('syncedAt', $syncedAt);

        return $query->getQuery()->getResult();
    }
}
