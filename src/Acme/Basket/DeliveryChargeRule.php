<?php

namespace Acme\Basket;

/**
 * Determines if a delivery charge rule is applicable to the order
 * and calculates the total price of the order including the delivery charge.
 *
 * @package Acme\Basket
 */
class DeliveryChargeRule
{
  public string|null $minPrice;
  public string|null $maxPrice;
  /**
   * The value that will be added to the order total if the delivery charge rule is applicable.
   * Negative and positive values can be used to create discounts or surcharges.
   */
  protected string|null $price;

  /**
   * Use this to test if an order total falls within the min and max price range for a certain delivery cost.
   * @param string|null $minPrice Minimum price range for the delivery charge rule to be applicable or null
   * @param string|null $maxPrice Maximum price range for the delivery charge rule to be applicable or null
   * @param string|null $price Price that will be added to the total when the delivery charge is applied or null (free $0.00)
   */
  public function __construct(string|null $minPrice, string|null $maxPrice, string|null $price)

  {
    /**
     * When the price isn't null then convert it into a string to maintain precision.
     */


    $this->price = $this->handleNullablePrice($price);

    $this->minPrice = $this->handleNullablePrice($minPrice);

    $this->maxPrice = $this->handleNullablePrice($maxPrice);
  }

  static public function handleNullablePrice(string|int|float|null $price)
  {
    if (!is_null($price)) {
      return strval($price);
    }
    return null;
  }

  /**
   * Calculates the total price of the order including the delivery charge.
   *
   * @param string $cartTotal Total price of the cart excluding taxes.
   * @return string Total price of the order including the delivery charge. When not eligible for the delivery charge, the cart total is returned.
   */
  public function getTotal(string $cartTotal): string
  {
    return $this->isEligible($cartTotal) ? bcadd($this->getPrice(), $cartTotal) : $cartTotal;
  }

  /**
   * Checks if the cart total falls within the min and max price range for the delivery charge rule.
   * minPrice <= cartTotal < maxPrice
   *
   * Minimum and maximum limits are ignored when they are set to null.
   *
   * @param string $cartTotal Total price of the cart excluding taxes.
   * @return bool Delivery charge rule applies to this order or not.
   */
  public function isEligible(string|int|float $cartTotal): bool
  {
    if ($this->minPrice === null && $this->maxPrice === null) {
      return true;
    }

    if ($this->minPrice === null) {
      return bccomp($cartTotal, $this->maxPrice) === -1;
    }
    $minComparison = bccomp($cartTotal, $this->minPrice);

    if ($this->maxPrice === null) {

      return $minComparison === 1 || $minComparison === 0;
    }

    $maxComparison = bccomp($cartTotal, $this->maxPrice);

    // return $cartTotal >= $this->minPrice && $cartTotal < $this->maxPrice;
    return ($minComparison === 1 || $minComparison === 0) && $maxComparison === -1;
  }

  /**
   * Price that will be added to the total when the delivery charge is applied.
   * Returns 0.0 when the price is null.
   * @return string
   */
  public function getPrice(): string
  {
    if ($this->price === null) {
      return 0;
    }
    return $this->price;
  }

  /**
   * The minimum price range for the delivery charge rule to be applicable.
   * When null, there is no minimum price range.
   * @return string|null
   */
  public function getMinPrice(): ?string
  {
    return $this->minPrice;
  }

  /**
   * The maximum price range for the delivery charge rule to be applicable.
   * When null, there is no maximum price range.
   * @return string|null
   */
  public function getMaxPrice(): ?string
  {
    return $this->maxPrice;
  }
}
