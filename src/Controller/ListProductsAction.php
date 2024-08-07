<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TaxonRepository;
use BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler\DataHandlerInterface;
use BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler\PaginationDataHandlerInterface;
use BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler\SortDataHandlerInterface;
use BitBag\SyliusElasticsearchPlugin\Exception\TaxonNotFoundException;
use BitBag\SyliusElasticsearchPlugin\Finder\ShopProductsFinderInterface;
use BitBag\SyliusElasticsearchPlugin\Form\Type\ShopProductsFilterType;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Environment;

final class ListProductsAction
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var DataHandlerInterface */
    private $shopProductListDataHandler;

    /** @var SortDataHandlerInterface */
    private $shopProductsSortDataHandler;

    /** @var PaginationDataHandlerInterface */
    private $paginationDataHandler;

    /** @var ShopProductsFinderInterface */
    private $shopProductsFinder;

    /** @var TaxonRepository */
    private $taxonRepository;

    /** @var LocaleContextInterface */
    private $localeContext;

    /** @var Environment */
    private $twig;

    public function __construct(
        FormFactoryInterface $formFactory,
        DataHandlerInterface $shopProductListDataHandler,
        SortDataHandlerInterface $shopProductsSortDataHandler,
        PaginationDataHandlerInterface $paginationDataHandler,
        ShopProductsFinderInterface $shopProductsFinder,
        Environment $twig,
        TaxonRepository $taxonRepository,
        LocaleContextInterface $localeContext
    ) {
        $this->formFactory = $formFactory;
        $this->shopProductListDataHandler = $shopProductListDataHandler;
        $this->shopProductsSortDataHandler = $shopProductsSortDataHandler;
        $this->paginationDataHandler = $paginationDataHandler;
        $this->shopProductsFinder = $shopProductsFinder;
        $this->twig = $twig;
        $this->taxonRepository = $taxonRepository;
        $this->localeContext = $localeContext;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(ShopProductsFilterType::class);

        $form->handleRequest($request);

        $requestData = array_merge(
            $form->getData(),
            $request->query->all(),
            ['slug' => $request->get('slug'), 'vehicle' => $request->get('vehicle')]
        );

        if (!$form->isValid())
            $requestData = $this->clearInvalidEntries($form, $requestData);

        $data = array_merge(
            $this->shopProductListDataHandler->retrieveData($requestData),
            $this->shopProductsSortDataHandler->retrieveData($requestData),
            $this->paginationDataHandler->retrieveData($requestData)
        );

        $template = $request->get('template');

        $vehicle = $this->taxonRepository->findOneBySlug($request->get('vehicle'), $this->localeContext->getLocaleCode());

        if (null === $vehicle)
            throw new TaxonNotFoundException();

        $data['taxon'] = [$data['taxon'], $vehicle];
        $products = $this->shopProductsFinder->find($data);

        return new Response($this->twig->render($template, [
            'form' => $form->createView(),
            'products' => $products,
            'taxon' => $data['taxon'][0],
            'vehicle' => $data['taxon'][1],
        ]));
    }

    private function clearInvalidEntries(FormInterface $form, array $requestData): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($form->getErrors(true, true) as $error) {
            $errorOrigin = $error->getOrigin();
            $propertyAccessor->setValue(
                $requestData,
                ($errorOrigin->getParent()->getPropertyPath() ?? '') . $errorOrigin->getPropertyPath(),
                ''
            );
        }

        return $requestData;
    }
}
