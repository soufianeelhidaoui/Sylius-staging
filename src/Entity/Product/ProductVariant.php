<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Channel\Channel;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Sylius\Component\Core\Model\ProductVariantInterface as BaseProductVariantInterface;
use BitBag\SyliusElasticsearchPlugin\Model\ProductVariantInterface as BitBagElasticsearchPluginVariant;
use BitBag\SyliusElasticsearchPlugin\Model\ProductVariantTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant")
 */
class ProductVariant extends BaseProductVariant implements BaseProductVariantInterface, BitBagElasticsearchPluginVariant, JsonSerializable
{
    use ProductVariantTrait;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $synced_at;

    //used in mails
    private $quantity=1;

    public function jsonSerialize() {

        /** @var ProductOptionValue[] $optionsValues */
        $optionsValues = $this->getOptionValues();
        $options = [
            count($optionsValues)>0?$optionsValues[0]->getCode():null,
            count($optionsValues)>1?$optionsValues[1]->getCode():null,
            count($optionsValues)>2?$optionsValues[2]->getCode():null,
        ];

        $channelPricings = $this->getChannelPricings();
        $channelPricing = $channelPricings->get('default');

        return [
            'id'=>$this->getId(),
            'title'=>$this->getTitle(),
            'name'=>$this->getName(),
            'option1'=>$options[0],
            'option2'=>$options[1],
            'option3'=>$options[2],
            'sku'=>$this->getSku(),
            'requires_shipping'=>false,
            'available'=>$this->getAvailable(),
            'options'=>$options,
            'price'=>$channelPricing->getPrice(),
            'compare_at_price'=>$channelPricing->getOriginalPrice(),
        ];
    }

    public function getChannelPricing(String $code)
    {
        $channel = new Channel();
        $channel->setCode($code);

        return parent::getChannelPricingForChannel($channel);
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

    public function setQuantity(int $quantity){

        $this->quantity = $quantity;
    }

    public function getQuantity(){

        return $this->quantity;
    }

    public function getTitle(){
        return $this->getName();
    }

    public function getAvailable(){
        return $this->isInStock();
    }

    public function getSku(){
        return str_replace('_', ' ', $this->getCode());
    }

    public function getWeight_unit(){
        return 'kg';
    }

    public function getPrice(){

        $channelPricings = $this->getChannelPricings();
        $channelPricing = $channelPricings->get('default');

        return $channelPricing->getPrice();
    }

    public function getImage(){

        /** @var Product $product */
        $product = $this->getProduct();

        return $product->getFeatured_image();
    }

    public function getFinal_line_price(){
        return $this->getPrice()*$this->getQuantity();
    }

    public function getCompare_at_price(){

        $channelPricings = $this->getChannelPricings();
        $channelPricing = $channelPricings->get('default');

        return $channelPricing->getOriginalPrice();
    }

    public function getInventory_quantity(){

        return $this->getOnHand();
    }

    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }
}
