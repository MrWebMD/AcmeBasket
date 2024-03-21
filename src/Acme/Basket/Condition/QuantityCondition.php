<?php

namespace Acme\Basket\Condition;

/**
 * When a product is added to the basket, the quantity of the product must be within a certain range.
 * @packageAcme\Basket\Condition
 */
class QuantityCondition extends ConditionType
{

  protected int|null $min;
  protected int|null $max;

  /**
   * Use this to check if a product quantity is within a certain range.
   * @param int|null $min - The minimum quantity of the product in the basket. When null there is no minimum.
   * @param int|null $max - The maximum quantity of the product in the basket. When null there is no maximum.
   */
  public function __construct(int|null $min, int|null $max)
  {
    $this->min = $min;
    $this->max = $max;
  }

  /**
   * Test the condition on a product in the basket.
   * @param int $productQuantity - The quantity of the product in the basket
   * @return bool Pass or fail
   */
  public function test(int $productQuantity): bool
  {
    $greaterThanMin = $this->min === null || $productQuantity >= $this->min;
    $lessThanMax = $this->max === null || $productQuantity < $this->max;

    return $greaterThanMin && $lessThanMax;
  }
}
