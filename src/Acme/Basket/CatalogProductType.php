<?php

namespace Acme\Basket;

/**
 * Simple data structure to represent a product in the catalog, used for all the operations in the basket.
 * Can easily be modified to include more product details than just the defaults.
 *
 * @package Acme\Basket
 */

abstract class CatalogProductType
{

  /**
   * @var string $sku Product Stock Keeping Unit
   */
  protected string $sku;

  /**
   * @var string $name Product name
   */
  protected string $name;

  /**
   * @var string $price Product price
   */
  protected string $price;


  /**
   * CatalogProduct constructor.
   * @param string $sku
   * @param string $name
   * @param string $price
   */

  public function __construct(string $sku, string $name, string $price)

  {
    $this->sku = $sku;
    $this->name = $name;
    $this->price = strval($price);
  }

  /**
   * Get the SKU of a product
   * @return string
   */
  public function getSku(): string
  {
    return $this->sku;
  }

  /**
   * Get the full name of a product
   * @return string
   */

  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Get the price of a product
   * @return string
   */
  public function getPrice(): string
  {
    return $this->price;
  }
}
