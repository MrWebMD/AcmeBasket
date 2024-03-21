<?php

namespace Acme\Basket\PriceAdjustment;

/**
 * When an offer is applied, the price of the product may manipulate in various ways
 * The adjustment type is the interface that defines the methods that should
 * be implemented by the different types of adjustments. Create an adjustment type that implements
 * this interface and then pass it to the ProductOffer class to apply the adjustment.
 *
 * @packageAcme\Basket\PriceAdjustment
 */
interface AdjustmentType
{
  /**
   * Constants which identify an adjustment type if stored within a database.
   */

  /**
   * Fixed adjustments add or subtract a fixed amount from the total. Ex. +$15 or -$5
   */
  public const FIXED = "FIXED";
  /**
   * Percentage adjustments add or subtract a percentage from the total. Ex. +10% or -5%
   */
  public const PERCENTAGE = "PERCENTAGE";


  /**
   * @return string Precise integer stored as a string type.
   */
  public function getValue(): string;
}
