<?php

namespace App\Entity;

use App\Repository\StructureParametersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StructureParametersRepository::class)
 */
class StructureParameters
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sell_drinks;

    /**
     * @ORM\Column(type="boolean")
     */
    private $manage_planning;

    /**
     * @ORM\Column(type="boolean")
     */
    private $shop;

    /**
     * @ORM\Column(type="boolean")
     */
    private $members_statistics;

    /**
     * @ORM\Column(type="boolean")
     */
    private $payment_management;

    /**
     * @ORM\Column(type="integer")
     */
    private $franchise_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $structure_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isSellDrinks(): ?bool
    {
        return $this->sell_drinks;
    }

    public function setSellDrinks(bool $sell_drinks): self
    {
        $this->sell_drinks = $sell_drinks;

        return $this;
    }

    public function isManagePlanning(): ?bool
    {
        return $this->manage_planning;
    }

    public function setManagePlanning(bool $manage_planning): self
    {
        $this->manage_planning = $manage_planning;

        return $this;
    }

    public function isShop(): ?bool
    {
        return $this->shop;
    }

    public function setShop(bool $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function isMembersStatistics(): ?bool
    {
        return $this->members_statistics;
    }

    public function setMembersStatistics(bool $members_statistics): self
    {
        $this->members_statistics = $members_statistics;

        return $this;
    }

    public function isPaymentManagement(): ?bool
    {
        return $this->payment_management;
    }

    public function setPaymentManagement(bool $payment_management): self
    {
        $this->payment_management = $payment_management;

        return $this;
    }

    public function getFranchiseId(): ?int
    {
        return $this->franchise_id;
    }

    public function setFranchiseId(int $franchise_id): self
    {
        $this->franchise_id = $franchise_id;

        return $this;
    }

    public function getStructureId(): ?int
    {
        return $this->structure_id;
    }

    public function setStructureId(int $structure_id): self
    {
        $this->structure_id = $structure_id;

        return $this;
    }
}
