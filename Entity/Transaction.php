<?php

namespace Padam87\SimplePayBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
class Transaction
{
    public const STATUS_STARTED = 'STARTED';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_FAIL = 'FAIL';
    public const STATUS_TIMEOUT = 'TIMEOUT';
    public const STATUS_CANCEL = 'CANCEL';
    public const STATUS_FINISHED = 'FINISHED';

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $cardId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $token = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    protected ?string $salt = null;

    #[ORM\Column]
    protected ?string $merchant = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    protected ?string $orderRef = null;

    #[Assert\Currency]
    #[Assert\Choice(['HUF', 'EUR', 'USD'])]
    #[ORM\Column]
    protected string $currency = 'HUF';

    #[Assert\NotBlank]
    #[ORM\Column]
    protected ?string $customerEmail = null;

    #[Assert\NotBlank]
    #[Assert\Language]
    #[ORM\Column]
    protected string $language = 'hu';

    #[Assert\Count(min: 1)]
    #[ORM\Column(type: 'simple_array')]
    protected array $methods = ['CARD'];

    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(type: 'float')]
    protected float $total = 0.0;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'integer')]
    protected int $timeout = 600;

    #[Assert\Valid]
    #[ORM\Embedded(class: Invoice::class)]
    protected Invoice $invoice;

    #[Assert\Valid]
    #[ORM\Embedded(class: Delivery::class)]
    protected ?Delivery $delivery = null;

    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(type: 'float')]
    protected float $shippingPrice = 0.0;

    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(type: 'float')]
    protected float $discount = 0.0;

    #[Assert\Valid]
    #[ORM\Embedded(class: Browser::class)]
    protected ?Browser $browser = null;

    #[Assert\Valid]
    #[ORM\Embedded(class: Recurring::class)]
    protected ?Recurring $recurring = null;

    /**
     * @var Item[]|Collection
     */
    #[Assert\Valid]
    protected Collection $items;

    #[ORM\Column]
    protected ?string $status = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $responseCode = null;

    public function __construct()
    {
        $this->salt = bin2hex(openssl_random_pseudo_bytes(16));

        $this->items = new ArrayCollection();
        $this->invoice = new Invoice();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCardId(): ?int
    {
        return $this->cardId;
    }

    public function setCardId(?int $cardId): self
    {
        $this->cardId = $cardId;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getMerchant(): ?string
    {
        return $this->merchant;
    }

    public function setMerchant(?string $merchant): self
    {
        $this->merchant = $merchant;

        return $this;
    }

    public function getOrderRef(): ?string
    {
        return $this->orderRef;
    }

    public function setOrderRef(?string $orderRef): self
    {
        $this->orderRef = $orderRef;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(?string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getMethods(): ?array
    {
        return $this->methods;
    }

    public function setMethods(array $methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems($items): self
    {
        $this->items->clear();

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    public function addItem(Item $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function removeItem(Item $item): self
    {
        $this->items->removeElement($item);

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    public function setShippingPrice($shippingPrice)
    {
        $this->shippingPrice = $shippingPrice;

        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    public function getBrowser(): ?Browser
    {
        return $this->browser;
    }

    public function setBrowser(?Browser $browser): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function isRecurring(): bool
    {
        if ($this->recurring === null) {
            return false;
        }

        return  $this->recurring->getTimes() !== null;
    }

    public function getRecurring(): ?Recurring
    {
        return $this->recurring;
    }

    public function setRecurring(?Recurring $recurring): self
    {
        $this->recurring = $recurring;

        return $this;
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [self::STATUS_SUCCESS, self::STATUS_FINISHED]);
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getResponseCode(): ?int
    {
        return $this->responseCode;
    }

    public function setResponseCode(?int $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }
}
