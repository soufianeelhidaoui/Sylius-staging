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

use App\Entity\Taxonomy\Taxon;
use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonTranslationType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class TaxonType extends AbstractResourceType
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
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => TaxonTranslationType::class
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false
            ])
            ->addEventSubscriber(new AddCodeFormSubscriber(null, ['constraints' => [
	            new NotBlank()
            ]]))
            ->add('parent', TaxonAutocompleteChoiceType::class, [
                'required' => false
            ])
            ->add('tags', TextType::class, [
                'required' => false
            ])
            ->add('color', TextType::class, [
                'required' => false
            ])
            ->add('quotation', CheckboxType::class, [
                'required' => false
            ])
            ->add('breadcrumb', CheckboxType::class, [
                'required' => false
            ])
	        ->add('medias', CollectionType::class, [
		        'entry_type' => MediaType::class,
		        'allow_add' => true,
		        'allow_delete' => true,
		        'by_reference' => false,
		        'delete_empty' => true
	        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Taxon::class,
            'csrf_protection' => false,
        ]);
    }
}
