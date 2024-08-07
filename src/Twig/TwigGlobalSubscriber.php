<?php

namespace App\Twig;

use App\Entity\Taxonomy\Taxon;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class TwigGlobalSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Twig\Environment
     */
    private $twig;
    private $container;
    private $settingsRegistry;
    private $menuRepository;
    private $taxonRepository;
    private $localeContext;
    private $router;

    public function __construct(Environment $twig, LocaleContextInterface $localeContext, ContainerInterface $container, TaxonRepository $taxonRepository, RepositoryInterface $menuRepository, RegistryInterface $settingsRegistry, UrlGeneratorInterface $router)
    {
        $this->twig = $twig;
        $this->container = $container;
        $this->settingsRegistry = $settingsRegistry;
        $this->menuRepository = $menuRepository;
        $this->taxonRepository = $taxonRepository;
        $this->localeContext = $localeContext;
        $this->router = $router;
    }

    public function injectGlobalVariables(ControllerEvent $event)
    {
        // Settings
        $settings = [];
        $channel = $this->container->get('sylius.context.channel')->getChannel();
        $data_expiry_time_ms=getenv('DATA_EXPIRY_TIME_MS');
        /** @var SettingsInterface[] $settings */
        if ($allSettings = $this->settingsRegistry->getAllSettings()) {
            foreach ($allSettings as $setting){
                $settings = array_merge($settings, $setting->getSettingsValuesByChannelAndLocale($channel));
            }
        }
        $this->twig->addGlobal('settings', $settings);
        $this->twig->addGlobal('data_expiry_time_ms', $data_expiry_time_ms);

        $route = $event->getRequest()->attributes->get('_route');
        $template = str_replace('sylius_shop_','', str_replace('bitbag_sylius_elasticsearch_plugin_shop_','', $route));

        if( $template == 'homepage')
            $template = 'index';
        elseif( $template == 'product_in_collection' || $template == 'product_show')
            $template = 'product';
        elseif( $template == 'cart_summary')
            $template = 'cart';
        elseif( $template == 'checkout_start')
            $template = 'cart-checkout';
        elseif( $template == 'collection_vehicle')
            $template = 'collection';

        $this->twig->addGlobal('template', $template);

        // Menus
        $menus = [];

        //todo: get codes dynamically
        foreach (['footer','sub_footer','sidebar'] as $code){

            if( $menu = $this->menuRepository->findOneByLocaleAndCode($this->localeContext->getLocaleCode(),$code) )
                $menus[$code] = $menu->getFirstLevelItems();
        }

        $this->twig->addGlobal('menu', $menus);
        $this->twig->addGlobal('current_tags', []);

        $this->twig->addGlobal('blank', null);


	    /** @var Taxon $accessories */
	    $accessories = $this->taxonRepository->findOneBy(['code'=>'accessories']);

        $this->twig->addGlobal('shop', [
            'url' => trim($this->router->generate('sylius_shop_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),'/'),
            'name' => $settings['brand'],
            'locale' => $this->localeContext->getLocaleCode(),
            'money_format' => '{{amount_with_comma_separator}}€',
            'currency' => 'EUR'
        ]);

        $this->twig->addGlobal('routes', [
            'root_url' => $this->router->generate('sylius_shop_homepage'),
            'cart_url' => $this->router->generate('sylius_shop_cart_summary'),
            'search_url' => $this->router->generate('bitbag_sylius_elasticsearch_plugin_shop_search'),
            'checkout_url' => $this->router->generate('sylius_shop_checkout_start'),
            'validate_url' => $this->router->generate('sylius_shop_cart_validate'),
            'all_products_collection_url' => $this->router->generate('sylius_shop_collection', ['slug'=>$accessories->getSlug()])
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER => 'injectGlobalVariables'];
    }
}
