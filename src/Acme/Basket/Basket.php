<?php

namespace Acme\Basket;

use Acme\Basket\Offers\ProductOffer;
use Exception;

/**
 * Default amount of decimal places used for financial calculations with the ext-bcmath library.
 * This prevents rounding errors when handling currency.
 */
const BASKET_BC_SCALE = 4;

bcscale(BASKET_BC_SCALE);

/**
 * @package Acme\Basket
 */
class Basket extends BasketType
{
  protected array $items = [];

  /**
   * @throws Exception Product wasn't found in the catalog
   */
  public function add($productSku): void
  {
    if (!$this->productIsInCatalog($productSku)) {
      throw new Exception("Product not found in catalog");
    }
    if (!$this->productIsInBasket($productSku)) {
      $this->items[$productSku] = 0;
    }
    $this->items[$productSku]++;
  }

  public function remove($productSku): void
  {
    /**
     * Decrement the quantity of the product in the basket
     */
    if ($this->productIsInBasket($productSku)) {
      $this->items[$productSku]--;
    }
    /**
     * Remove the product from the basket if the quantity is 0
     */
    if ($this->items[$productSku] <= 0) {
      unset($this->items[$productSku]);
    }
  }

  public function clear(): void
  {
    $this->items = [];
  }

  public function countUniqueItems(): int
  {
    return count($this->items);
  }

  public function countTotalItems(): int
  {
    return array_sum($this->items);
  }

  public function productHasOffer(string $productSku): bool
  {
    return key_exists($productSku, $this->offers);
  }

  public function getProductOffer(string $productSku): ProductOffer|null
  {
    if (!$this->productHasOffer($productSku)) {
      return null;
    }
    return $this->offers[$productSku];
  }

  public function productIsInCatalog(string $productSku): bool
  {
    return in_array($productSku, $this->catalogProductSkus);
  }

  public function productIsInBasket(string $productSku): bool
  {
    return isset($this->items[$productSku]);
  }


  public function getDeliveryCost(string $total): string
  {
    $deliveryCosts = "0";

    foreach ($this->deliveryChargeRules as $rule) {
      if ($rule->isEligible($total)) {
        $deliveryCosts = bcadd($deliveryCosts, $rule->getPrice());
      }
    }

    return $deliveryCosts;
  }

  public function getTotal(): string
  {

    $total = "0";

    foreach ($this->items as $productSku => $quantity) {
      $product = $this->catalogProducts[$productSku];

      $productSku = $product->getSku();

      $offer = $this->getProductOffer($productSku);

      /**
       * If the product does not have an applicable offer
       * than the default total of that product is calculated
       */


      $subTotal = bcmul($product->getPrice(), $quantity);

      if (
        $offer == null ||
        !$this->getProductOffer($productSku)->isEligible($quantity)
      ) {
        $total = bcadd($total, $subTotal);
        continue;
      }

      /**
       * Product has an applicable offer
       */

      $total = bcadd($total, $offer->getTotal($subTotal));
    }

    if ($total <= 0) {
      return 0;
    }

    $total = bcadd($total, $this->getDeliveryCost($total));

    return $total;
  }
}
