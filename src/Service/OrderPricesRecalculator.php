<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Calculator\ProductVariantPriceCalculatorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Webmozart\Assert\Assert;

final class OrderPricesRecalculator implements OrderProcessorInterface
{
    private $kernel;
    private $entityManager;
    private $requestStack;
    private $productVariantPriceCalculator;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager, ProductVariantPriceCalculatorInterface $productVariantPriceCalculator, RequestStack $requestStack)
    {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->productVariantPriceCalculator = $productVariantPriceCalculator;
    }

    public function process(BaseOrderInterface $order): void
    {
        Assert::isInstanceOf($order, OrderInterface::class);

        $route = $this->requestStack->getCurrentRequest()->get('_route');

        if ($order->isEmpty() || $route == 'sylius_shop_cart_item_remove')
            return;

        $prices = [];
        $channel = $order->getChannel();

        if($serviceCenterId = $_REQUEST['partnerId']??false){

            $order->setServiceCenterId($serviceCenterId);

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $filesystem = new Filesystem();
            $priceDir = $this->kernel->getProjectDir().'/private/service_center/prices/';

            $prices_file = $priceDir.$serviceCenterId.'.json';

            if( $filesystem->exists($prices_file) )
                $prices = json_decode(file_get_contents($prices_file), true);
        }

        foreach ($order->getItems() as $item) {

            if ( $item->isImmutable() )
                continue;

            /** @var ProductVariantInterface $variant */
            $variant = $item->getVariant();

            $item->setUnitPrice($this->productVariantPriceCalculator->calculate(
                $item->getVariant(),
                ['channel' => $channel]
            ));

            $code = $variant->getCode();

            if( !$price = $prices[$code]['vendorPriceTTC']??0 )
                continue;

            $item->setUnitPrice($price*100);
        }
    }
}
