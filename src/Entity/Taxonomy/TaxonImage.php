<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\TaxonImage as BaseTaxonImage;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_taxon_image")
 */
class TaxonImage extends BaseTaxonImage
{
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */

	public $updated_at;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	public $synced_at;

	/**
	 * @return mixed
	 */
	public function getUpdatedAt():?\DateTimeInterface
	{
		return $this->updated_at;
	}

	/**
	 * @param mixed $updated_at
	 */
	public function setUpdatedAt(?\DateTimeInterface $updated_at): self
	{
		$this->updated_at = $updated_at;

		return $this;
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
}
