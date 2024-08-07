<?php

/*
 * This file is part of Monsieur Biz' Settings plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Form;

use MonsieurBiz\SyliusSettingsPlugin\Form\AbstractSettingsType;
use MonsieurBiz\SyliusSettingsPlugin\Form\SettingsTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SettingsToolsType extends AbstractSettingsType implements SettingsTypeInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gmap_api', TextType::class, [
                'label' => 'Google map API Key',
                'data_class' => null,
                'required' => false
            ])
            ->add('ga4', TextType::class, [
                'label' => 'GA 4',
                'data_class' => null,
                'required' => false
            ])
            ->add('ga', TextType::class, [
                'label' => 'GA',
                'data_class' => null,
                'required' => false
            ])
            ->add('gtm', TextType::class, [
                'label' => 'GTM',
                'data_class' => null,
                'required' => false
            ])
            ->add('cookiebot', TextType::class, [
                'label' => 'Cookiebot',
                'data_class' => null,
                'required' => false
            ])
            ->add('bugherd', TextType::class, [
                'label' => 'Bugherd',
                'data_class' => null,
                'required' => false
            ])
            ->add('hotjar', TextType::class, [
                'label' => 'Hotjar',
                'data_class' => null,
                'required' => false
            ])
            ->add('external_scripts', TextareaType::class, [
                'label' => 'External Scripts',
                'data_class' => null,
                'required' => false
            ])
            ->add('datalayer', CheckboxType::class, [
                'label' => 'Enable datalayer',
                'data_class' => null,
                'required' => false
            ]);
    }
}
