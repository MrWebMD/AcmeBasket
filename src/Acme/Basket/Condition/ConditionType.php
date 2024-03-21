<?php

namespace Acme\Basket\Condition;

/**
 * Offers may be applied conditionally, use this class to create various conditions
 * to test against a product in the basket.
 */
abstract class ConditionType
{
  /**
   * Test a condition against a product in the basket.
   * @param int $productQuantity - The quantity of this product in the basket
   * @return bool - Pass or fail
   */
  abstract public function test(int $productQuantity): bool;

  /**
   * Test an array of basket conditions, if one fails then the collection of conditions returns false.
   * @param ConditionType[] $conditions - Array of conditions to test
   * @param int $quantity - The quantity of this product in the basket
   * @return bool - True when all conditions pass, false when one or all conditions fail.
   */
  static public function testAllConditions(array $conditions, int $quantity): bool
  {
    foreach ($conditions as $condition) {
      if (!$condition->test($quantity)) {
        return false;
      }
    }
    return true;
  }
}
