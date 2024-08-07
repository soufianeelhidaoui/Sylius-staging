<?php

namespace App\Form\Extension;

use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;

final class TaxonTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tags', TextType::class, [
                'required' => false
            ])
            ->add('quotation', CheckboxType::class, [
                'required' => false,
                'label' => 'Require quotation'
            ])
            ->add('color', ColorType::class, [
                'required' => false
            ])
            ->add('breadcrumb', CheckboxType::class, [
                'required' => false,
                'label' => 'Show in breadcrumb'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [TaxonType::class];
    }
}
