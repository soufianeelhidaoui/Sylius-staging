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

use MonsieurBiz\SyliusMenuPlugin\Entity\MenuInterface;
use MonsieurBiz\SyliusMenuPlugin\Hydrator\MenuTreeHydrator;
use MonsieurBiz\SyliusMenuPlugin\Repository\MenuRepository as BaseMenuRepository;

class MenuRepository extends BaseMenuRepository
{
    public function findAllByLocale(string $localeCode): ?MenuInterface
    {
        $queryBuilder = $this->createQueryBuilder('o');
        $queryBuilder
            ->addSelect('item')
            ->addSelect('item_translation')
            ->innerJoin('o.items', 'item')
            ->innerJoin('item.translations', 'item_translation', 'WITH', 'item_translation.locale = :locale')
            ->setParameter('locale', $localeCode);

        return (new MenuTreeHydrator())($queryBuilder->getQuery()->getOneOrNullResult());
    }
}
