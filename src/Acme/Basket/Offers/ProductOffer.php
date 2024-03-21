<?php

namespace Acme\Basket\Offers;

use Acme\Basket\CatalogProduct;
use Acme\Basket\Condition\ConditionType;
use Acme\Basket\PriceAdjustment\AdjustmentType;

/**
 * When a product in the basket is eligible for an offer,
 * this class is used to calculate the subTotal of that product in the basket
 * including its quantity and any price adjustments.
 *
 * @packageAcme\Basket\Offers
 */
class ProductOffer
{
  /** @var CatalogProduct */
  protected CatalogProduct $catalogProduct;

  /** @var ConditionType[] */
  protected array $conditions;

  /** @var AdjustmentType[] */
  protected array $priceAdjustments;

  protected string $offerTitle;

  protected string $offerDescription;

  /**
   * Create a new product offer
   * @param string $offerTitle The name of the offer ex. By one get one free
   * @param string $offerDescription A description of the offer ex. Limited time sale for March of 2024
   * @param ConditionType[] $conditions The conditions that must be met before the offer can be applied
   * @param AdjustmentType[] $priceAdjustments Price adjustments that are made when the offer is applied
   */
  public function __construct(
    string         $offerTitle,
    string         $offerDescription,
    array          $conditions,
    array          $priceAdjustments
  ) {
    $this->offerTitle = $offerTitle;
    $this->offerDescription = $offerDescription;
    $this->conditions = $conditions;
    $this->priceAdjustments = $priceAdjustments;
  }

  /**
   * Get the total price of the product in the basket including any price adjustments
   * @param string $productSubTotal Price of the product multiplied by its quantity
   */
  public function getTotal(string $productSubTotal): string
  {
    foreach ($this->priceAdjustments as $priceAdjustment) {
      $productSubTotal = bcadd(
        $productSubTotal,
        $priceAdjustment->getValue(),
      );
    }
    return $productSubTotal;
  }

  public function isEligible(int $productQuantity): bool
  {
    return ConditionType::testAllConditions(
      $this->conditions,
      $productQuantity
    );
  }
}
