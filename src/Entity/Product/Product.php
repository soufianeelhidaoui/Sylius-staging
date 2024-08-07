<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Taxonomy\Taxon;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct
{

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $synced_at;

	public $medias;

	public function getMedias(){

		return $this->medias;
	}

    public function getSku(): ?string{

        return str_replace('_', ' ', $this->getCode());
    }

    public function setMedias($medias){

		return $this->medias = $medias;
	}

    public function getDescription(): ?string
    {
        $description = parent::getDescription();

        $description = str_replace('<br/>', "\n", $description);
        $description = str_replace('<br>', "\n", $description);

        return strip_tags($description);
    }

    /**
     * @return ProductVariant|mixed
     */
    public function getVariant(){

        $variants = $this->getVariants();
        return $variants[0]??false;
    }

	/**
	 * @return mixed
	 */
	public function getSyncedAt():?\DateTimeInterface
	{
		return $this->synced_at;
	}

	/**
	 * @param mixed $synced_at
	 */
	public function setSyncedAt(?\DateTimeInterface $synced_at): self
	{
		$this->synced_at = $synced_at;

		return $this;
	}

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }

    public function getTitle(){

        return $this->getName();
    }

    public function getMedia(){

        return $this->getImages();
    }

    public function getCollections(){

        /** @var Taxon[] $taxons */
        $taxons = $this->getTaxons();
        $collections = [];

        foreach ($taxons as $taxon){

            if( $taxon->isEnabled() )
                $collections[] = $taxon;
        }

        usort($collections, function($a,$b){
            $b_parent = $b->getParent();
            return $a->getId()-($b_parent?$b_parent->getId():0);
        } );

        return $collections;
    }

    public function getHas_compatibility(){

        /** @var Taxon[] $taxons */
        $taxons = $this->getCollections();

        foreach ($taxons as $taxon){

            if( $taxon->getChildOf('vehicles') )
                return true;
        }

        return false;
    }

    public function getVehicles(){

        /** @var Taxon[] $taxons */
        $taxons = $this->getCollections();
        $vehicles = [];

        foreach ($taxons as $taxon){

            if( $taxon->getChildOf('vehicles') )
                $vehicles[] = $taxon;
        }

        return $vehicles;
    }

    public function getOptions_with_values(){

        $options = [];

        /** @var ProductOption[] $productOptions */
        $productOptions = $this->getOptions();

        foreach ($productOptions as $index=>$productOption){

            /** @var ProductOptionValue[] $productOptionsValues */
            $productOptionsValues = $productOption->getValues();
            $values = [];
            foreach ($productOptionsValues as $productOptionsValue){
                $values[] = $productOptionsValue->getCode();
            }
            $options[] = ['name'=>$productOption->getName(), 'code'=>$productOption->getCode(), 'position'=>$index+1, 'values'=>$values];
        }
        return $options;
    }

    public function getSelected_or_first_available_variant(){

        return $this->getEnabledVariants()->first();
    }

    public function getAvailable(){

        return count($this->getEnabledVariants())>0;
    }

    public function getUrl(){

        return $this->getSlug();
    }

    public function getTags(){

        if( $value = $this->getAttribute('tags') )
            return array_map('trim', explode(',', $value));

        return [];
    }

    public function getAttribute($code){

        /** @var ProductAttributeValue[] $attributes */
        $attributes = $this->getAttributes();

        foreach ($attributes as $attribute){

            if( $attribute->getCode() == $code )
                return empty($attribute->getValue()) ? false : $attribute->getValue();
        }

        return null;
    }

    public function getTotal_quantity(){

        $stock = 0;

        /** @var ProductVariant[] $variants */
        if( !$variants = $this->getEnabledVariants() )
            return $stock;

        foreach ($variants as $variant)
            $stock += $variant->getOnHand();

        return $stock;
    }

	public function getImage($type=false){

		/** @var ProductImage[] $images */
		$images = $this->getImages();

		if( $type ){

			foreach ($images as $image){

				if( $image->getType() == $type )
					return $image;
			}

			return false;
		}

		return $images[0]??false;
	}

    public function getFeatured_image(){

        $images = $this->getImagesByType('main');

        if( !$images->count() )
            $images = $this->getImages();

        if( $images->count() )
            return $images->first()->getPath();

        return false;
    }

    public function getPrice(){

        /** @var ProductVariant[] $variants */
        if( !$variants = $this->getEnabledVariants() )
            return 0;

        $min = false;

        foreach ($variants as $variant){

            $channelPricings = $variant->getChannelPricings();

            if( !$channelPricing = $channelPricings->get('default') )
                return 0;

            $price = $channelPricing->getPrice();

            if( $min === false ){

                $min = $price;
            }
            else{

                $min = min($min, $price);
            }
        }

        return $min;
    }

    public function getCompare_at_price(){

        /** @var ProductVariant[] $variants */
        if( !$variants = $this->getEnabledVariants() )
            return 0;

        $min = false;

        foreach ($variants as $variant){

            $channelPricings = $variant->getChannelPricings();

            if( !$channelPricing = $channelPricings->get('default') )
                return 0;

            $price = $channelPricing->getOriginalPrice();

            if( $min === false ){

                $min = $price;
            }
            else{

                $min = min($min, $price);
            }
        }

        return $min;
    }

    public function getRequire_quotation(){

        /** @var Taxon[] $taxons */
        $taxons = $this->getTaxons();

        foreach ($taxons as $taxon ){

            if( $taxon->getQuotation() )
                return true;
        }

        return false;
    }

    public function getPrice_varies(){

        /** @var ProductVariant[] $variants */
        if( !$variants = $this->getEnabledVariants() )
            return 0;

        $min = $max = false;

        foreach ($variants as $variant){

            $channelPricings = $variant->getChannelPricings();

            if( !$channelPricing = $channelPricings->get('default') )
                return 0;

            $price = $channelPricing->getPrice();

            if( $min === false ){

                $min = $max = $price;
            }
            else{

                $min = min($min, $price);
                $max = max($max, $price);
            }
        }

        return $max != $min;
    }
}
