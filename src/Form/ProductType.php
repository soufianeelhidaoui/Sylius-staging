<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Form;

use App\Entity\Product\Product;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\CoreBundle\Form\Type\Taxon\ProductTaxonAutocompleteChoiceType;
use Sylius\Bundle\ProductBundle\Form\EventSubscriber\BuildAttributesFormSubscriber;
use Sylius\Bundle\ProductBundle\Form\EventSubscriber\ProductOptionFieldSubscriber;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAssociationsType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAttributeValueType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductTranslationType;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductType extends AbstractResourceType
{
    private ProductVariantResolverInterface $variantResolver;

    private FactoryInterface $attributeValueFactory;

    private TranslationLocaleProviderInterface $localeProvider;

    /**
     * @param array|string[] $validationGroups
     */
    public function __construct(string $dataClass, array $validationGroups, ProductVariantResolverInterface $variantResolver, FactoryInterface $attributeValueFactory, TranslationLocaleProviderInterface $localeProvider)
    {
        parent::__construct($dataClass, $validationGroups);

        $this->variantResolver = $variantResolver;
        $this->attributeValueFactory = $attributeValueFactory;
        $this->localeProvider = $localeProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber())
            ->addEventSubscriber(new ProductOptionFieldSubscriber($this->variantResolver))
            ->addEventSubscriber(new BuildAttributesFormSubscriber($this->attributeValueFactory, $this->localeProvider))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $product = $event->getData();
                $form = $event->getForm();

                $form->add('productTaxons', ProductTaxonAutocompleteChoiceType::class, [
                    'product' => $product,
                    'multiple' => true
                ]);
            })
            ->add('enabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ProductTranslationType::class
            ])
            ->add('attributes', CollectionType::class, [
                'entry_type' => ProductAttributeValueType::class,
                'required' => false,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
            ->add('associations', ProductAssociationsType::class, [])
            ->add('channels', ChannelChoiceType::class, [
                'multiple' => true
            ])
            ->add('mainTaxon', TaxonAutocompleteChoiceType::class, [])
            ->add('variantSelectionMethod', ChoiceType::class, [
                'choices' => array_flip(\Sylius\Component\Core\Model\Product::getVariantSelectionMethodLabels()),
                'empty_data' => 'choice'
            ])
	        ->add('medias', CollectionType::class, [
		        'entry_type' => MediaType::class,
		        'allow_add' => true,
		        'allow_delete' => true,
		        'by_reference' => false,
		        'delete_empty' => true
	        ])
	        ->add('channelPricings', CollectionType::class, [
                'entry_type' => ChannelPricingType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'mapped' => false
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Product::class,
            'csrf_protection' => false
        ]);
    }
}
