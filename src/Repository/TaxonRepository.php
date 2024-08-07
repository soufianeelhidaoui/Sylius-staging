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

use App\Entity\Taxonomy\Taxon;
use App\Entity\Taxonomy\TaxonImage;
use App\Entity\Taxonomy\TaxonTranslation;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository as BaseTaxonRepository;

class TaxonRepository extends BaseTaxonRepository
{
    public function findOneByCode($code){

        $code = str_replace('-', '_', $code);

        return $this->createQueryBuilder('t')
            ->where('t.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hydrate(Taxon $_taxon){

        $taxon = [
            'id' => $_taxon->getId(),
            'code' => $_taxon->getCode(),
            'parent' => $_taxon->getParent()?$_taxon->getParent()->getCode():null,
            'tags' => $_taxon->getTags(),
            'color' => $_taxon->getColor(),
            'quotation' => $_taxon->getQuotation(),
            'breadcrumb' => $_taxon->getBreadcrumb(),
	        'createdAt' => $_taxon->getCreatedAt(),
	        'updatedAt' => $_taxon->getUpdatedAt(),
	        'syncedAt' => $_taxon->getSyncedAt(),
            'images' => [],
            'translations' => []
        ];

        /** @var TaxonTranslation[] $translations */
        $translations = $_taxon->getTranslations();

        foreach ($translations as $translation){

            $taxon['translations'][$translation->getLocale()] = [
                'name' => $translation->getName(),
                'slug' => $translation->getSlug(),
                'description' => $translation->getDescription()
            ];
        }

        /** @var TaxonImage[] $images */
        $images = $_taxon->getImages();

        foreach ($images as $image){

            $taxon['images'][] = [
                'path' => $image->getPath(),
                'type' => $image->getType(),
                'updatedAt' => $image->getUpdatedAt(),
                'syncedAt' => $image->getSyncedAt()
            ];
        }

        return $taxon;
    }
}
