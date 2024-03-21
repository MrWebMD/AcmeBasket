<?php

namespace Acme\Basket\PriceAdjustment;

/**
 * Apply a fixed adjustment to some total. ex. +$15 or -$5
 * @packageAcme\Basket\PriceAdjustment
 */
class FixedAdjustment implements AdjustmentType
{
  protected string $adjustment;

  /**
   * @param string $adjustment Price that this adjustment will add or subtract from some total. ex. 15 or -5 . Use negative numbers for discounts
   */
  public function __construct(string $adjustment)
  {
    $this->adjustment = strval($adjustment);
  }

  /**
   * Get the value of the fixed price adjustment
   * @return string Precise price integer stored as a string type.
   */
  public function getValue(): string
  {
    return $this->adjustment;
  }
}
