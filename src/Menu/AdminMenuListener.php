<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $newSubmenu = $menu
            ->addChild('new')
            ->setLabel('Réglages des mentions légales');

        $newSubmenu
            ->addChild('new-subitem', ['route' => 'app_admin_legalContent_index'])
            ->setLabel('Mentions légales')
            ->setLabelAttribute('icon', 'bullseye');
    }
}
