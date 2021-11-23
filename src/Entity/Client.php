<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
{
    public function __construct()
    {
        $this->adresses = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * Many Client have Many Adresses.
     * @ORM\ManyToMany(targetEntity="Adresse")
     * @ORM\JoinTable(name="client_adresse",
     *      joinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="adresse_id", referencedColumnName="id")}
     * )
     */
    private $adresses;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresses(): ?ArrayCollection
    {
        return $this->adresses;
    }

    public function setAdresses(ArrayCollection $adresses): self
    {
        $this->adresses = $adresses;

        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'prenom,' => $this->getPrenom(),
            'nom' => $this->getNom(),
            'email' => $this->getEmail(),
            'adresses' => $this->getAdresses()
        ];
    }
}
