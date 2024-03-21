<?php

namespace Acme\Basket;

use Acme\Basket\Offers\ProductOffer;

/**
 * Track the items in the basket and calculate the total price and delivery costs.
 * Create special product offers, and delivery charge rules.
 *
 * This interface allows the developer to choose their implementation of the basket.
 * Work with a database, session, json or any other storage method to store the basket items,
 * while still being able to take advantage of the supporting data structures of the basket.
 *
 * @package Acme\Basket\Basket
 */
abstract class BasketType
{
  /** @var CatalogProduct[] */
  protected array $catalogProducts = [];

  /** @var DeliveryChargeRule[] */
  protected array $deliveryChargeRules = [];

  /** @var ProductOffer[] */
  protected array $offers = [];

  /** @var string[] */
  protected array $catalogProductSkus = [];

  /**
   *
   * Create a new basket instance
   * @param CatalogProduct[] $catalogProducts [sku => product] Where the key of the array is the product sku and the value is the product
   * @param DeliveryChargeRule[] $deliveryChargeRules
   * @param ProductOffer[] $offers
   */
  public function __construct(array $catalogProducts, array $deliveryChargeRules, array $offers)
  {
    $this->catalogProducts = $catalogProducts;
    $this->deliveryChargeRules = $deliveryChargeRules;
    $this->offers = $offers;

    foreach ($this->catalogProducts as $catalogProduct) {
      $this->catalogProductSkus[] = $catalogProduct->getSku();
    }
  }

  /**
   * Add a product to the basket
   * @param string $productSku
   */
  abstract public function add(string $productSku): void;

  /**
   * Remove a product from the basket
   * @param string $productSku
   */
  abstract public function remove(string $productSku): void;

  /**
   * Empty the basket of all contents while preserving the catalog.
   * @return void
   */
  abstract public function clear(): void;

  /**
   * Get the number of unique products in the basket
   * @return int The number of items in the basket
   */
  abstract public function countUniqueItems(): int;

  /**
   * Get the number of total products in the basket including the quantity of each product
   * @return int The number of total products in the basket
   */
  abstract public function countTotalItems(): int;

  /**
   * Check if a product has an offer associated with it.
   * @param string $productSku
   * @return bool
   */
  abstract public function productHasOffer(string $productSku): bool;

  /**
   * Get the offer for a product or null if one doesn't exist
   * @param string $productSku
   * @return ProductOffer|null
   */
  abstract public function getProductOffer(string $productSku): ProductOffer|null;

  /**
   * Check if the full basket catalog of products contains a product with a specific SKU
   * @param string $productSku
   * @return bool True if the product is in the catalog
   */
  abstract public function productIsInCatalog(string $productSku): bool;

  /**
   * Check if a specific product has been added to the basket.
   * @param string $productSku
   * @return bool True if the product is in the basket
   */
  abstract public function productIsInBasket(string $productSku): bool;

  /**
   * Get the delivery costs for the basket based on the total price of the items in the basket.
   * @param string $total Total price of the items in the basket.
   * @return string Delivery cost for the basket.
   */
  abstract public function getDeliveryCost(string $total): string;


  /**
   * Calculate the total of all items in the basket
   * @return string Total price of the items in the basket.
   */
  abstract public function getTotal(): string;
}
