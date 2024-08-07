<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Sylius\Component\Core\Model\Taxon as BaseTaxon;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_taxon")
 */
class Taxon extends BaseTaxon implements JsonSerializable
{
    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    public $tags;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    public $color;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    public $quotation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    public $breadcrumb;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $synced_at;


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

	public $medias;

    public function setCode(?string $code): void
    {
        $this->code = str_replace('-', '_', $code);
    }

    public function setTags(?string $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function getTag($id): ?string
    {
        if( !$this->tags )
            return null;

        $tags = explode(',', $this->tags);

        foreach ($tags as $tag){

            $tag = explode(':', $tag);
            if( trim($tag[0]) == $id )
                return trim($tag[1]??'');
        }

        return null;
    }

    public function setColor(?string $color)
    {
        $this->color = $color;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getChildOf($code){

        if( $this->getCode() == $code )
            return true;

        $taxon = $this;
        while( $taxon = $taxon->getParent() ){

            if( $taxon->getCode() == $code )
                return  true;
        }

        return false;
    }

    public function getIsActive($code){

        if( $this->getCode() == $code )
            return true;

        $taxons = $this->getChildren();

        foreach ($taxons as $taxon){

            if( $taxon->getCode() == $code )
                return  true;
        }

        return false;
    }

    public function getBreadcrumb(): ?bool
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(?bool $breadcrumb)
    {
        $this->breadcrumb = $breadcrumb;

        return $this;
    }

    public function getQuotation(): ?bool
    {
        return $this->quotation;
    }

    public function setQuotation(?bool $quotation)
    {
        $this->quotation = $quotation;

        return $this;
    }

    public function getHandle(){
        return $this->getCode();
    }

    public function jsonSerialize() {
        return $this->getCode();
    }

    protected function createTranslation(): TaxonTranslationInterface{
        return new TaxonTranslation();
    }

    public function getTitle(){

        return $this->getName();
    }

    public function getLabel(){

        return $this->getName();
    }

    public function getNew(){

        $tags = $this->getTags();

        if( !empty($tags) )
            return in_array('new', explode(',', $this->getTags()));

        return false;
    }

    public function getItems(){

        $items = [];

        /** @var Taxon[] $taxons */
        $taxons = $this->getChildren();

        foreach ($taxons as $taxon){

            if( $taxon->isEnabled() )
                $items[] = $taxon;
        }

        return $items;
    }

    public function getUrl(){

        return $this->getSlug();
    }

    public function getImage($type=false){

        /** @var TaxonImage[] $images */
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


	public function getMedias(){

		return $this->medias;
	}


	public function setMedias($medias){

		return $this->medias = $medias;
	}
}
