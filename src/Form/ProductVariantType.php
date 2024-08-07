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

use App\Entity\Product\ProductVariant;
use Sylius\Bundle\CoreBundle\Form\Type\ChannelCollectionType;
use Sylius\Bundle\CoreBundle\Form\Type\Product\ChannelPricingType;
use Sylius\Bundle\ProductBundle\Form\EventSubscriber\BuildProductVariantFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingCategoryChoiceType;
use Sylius\Bundle\TaxationBundle\Form\Type\TaxCategoryChoiceType;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductVariantType extends AbstractResourceType
{
	/**
	 * @param array|string[] $validationGroups
	 */
	public function __construct(string $dataClass, array $validationGroups)
	{
		parent::__construct($dataClass, $validationGroups);
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('enabled', CheckboxType::class, [
				'required' => false,
				'label' => 'sylius.form.product.enabled',
			])
			->add('version', HiddenType::class)
			->add('tracked', CheckboxType::class, [
				'label' => 'sylius.form.variant.tracked',
				'help' => 'sylius.form.variant.tracked_help',
			])
			->add('shippingRequired', CheckboxType::class, [
				'label' => 'sylius.form.variant.shipping_required',
			])

			->add('width', NumberType::class, [
				'required' => false,
				'label' => 'sylius.form.variant.width',
				'invalid_message' => 'sylius.product_variant.width.invalid',
			])
			->add('height', NumberType::class, [
				'required' => false,
				'label' => 'sylius.form.variant.height',
				'invalid_message' => 'sylius.product_variant.height.invalid',
			])
			->add('depth', NumberType::class, [
				'required' => false,
				'label' => 'sylius.form.variant.depth',
				'invalid_message' => 'sylius.product_variant.depth.invalid',
			])
			->add('weight', NumberType::class, [
				'required' => false,
				'label' => 'sylius.form.variant.weight',
				'invalid_message' => 'sylius.product_variant.weight.invalid',
			])
			->add('taxCategory', TaxCategoryChoiceType::class, [
				'required' => false,
				'placeholder' => '---',
				'label' => 'sylius.form.product_variant.tax_category',
			])
			->add('shippingCategory', ShippingCategoryChoiceType::class, [
				'required' => false,
				'placeholder' => 'sylius.ui.no_requirement',
				'label' => 'sylius.form.product_variant.shipping_category',
			])
			->addEventSubscriber(new AddCodeFormSubscriber())
		;

		$builder
			->addEventSubscriber(new BuildProductVariantFormSubscriber($builder->getFormFactory()))
			->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
			$productVariant = $event->getData();

			$event->getForm()->add('channelPricings', ChannelCollectionType::class, [
				'entry_type' => ChannelPricingType::class,
				'entry_options' => fn(ChannelInterface $channel) => [
					'channel' => $channel,
					'product_variant' => $productVariant,
					'required' => false,
				],
				'label' => 'sylius.form.variant.price',
			]);
		});
	}


	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class'      => ProductVariant::class,
			'csrf_protection' => false
		]);
	}
}
