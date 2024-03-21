<?php

namespace Tests\Unit;

use Acme\Basket\PriceAdjustment\FixedAdjustment;
use Acme\Basket\CatalogProduct;
use Acme\Basket\Condition\QuantityCondition;
use Acme\Basket\DeliveryChargeRule;
use Acme\Basket\Offers\ProductOffer;
use Acme\Basket\Basket;
use PHPUnit\Framework\TestCase;

class BasketTest extends TestCase
{

  /**
   * QuantityCondition
   */

  public Basket $basket;

  public array $productCatalog;
  public array $deliveryChargeRules;
  public array $productOffers;

  public function test_quantity_condition_range_passes_or_fails(): void
  {
    $condition = new QuantityCondition(1, 5);

    $product = new CatalogProduct('sku', 'Product Name', 0.00);

    $this->assertTrue($condition->test(3));
    $this->assertFalse($condition->test(10));
  }

  public function test_fixed_price_adjustment_returns_value(): void
  {
    $adjustment = new FixedAdjustment("-16.475");

    $this->assertEquals("-16.475", $adjustment->getValue());
  }

  /**
   * CatalogProduct
   */

  public function test_product_returns_sku_name_price(): void
  {
    $product = new CatalogProduct('sku', 'Product Name', 32.95);

    $this->assertEquals('sku', $product->getSku());
    $this->assertEquals('Product Name', $product->getName());
    $this->assertEquals('32.95', $product->getPrice());
  }

  public function test_product_offer_returns_total_and_determines_eligibility(): void
  {

    $offer = new ProductOffer(
      "Buy one red widget, get the second at half price",
      "Limited time offer",
      [
        new QuantityCondition(2, null)
      ],
      [
        new FixedAdjustment("-16.475")
      ]
    );

    $this->assertTrue($offer->isEligible(2));

    $this->assertFalse($offer->isEligible(1));



    $comparison = bccomp("33.525", $offer->getTotal("50"));

    $this->assertEquals(0, $comparison);
  }

  /**
   * DeliveryChargeRule
   */

  public function test_delivery_charge_returns_eligibility(): void
  {
    // $rule = new DeliveryChargeRule(50, 90, 2.95);

    // $this->assertTrue($rule->isEligible(60));
    // $this->assertFalse($rule->isEligible(40));

    $rule = new DeliveryChargeRule(null, null, 2.95);

    $this->assertTrue($rule->isEligible(60));
  }

  public function test_get_delivery_charge_total_with_cart(): void
  {
    $rule = new DeliveryChargeRule(50, 90, 2.95);

    $this->assertEquals(62.95, $rule->getTotal(60));

    $this->assertEquals(100, $rule->getTotal(100));
  }

  public function test_delivery_charge_returns_price(): void
  {
    $rule = new DeliveryChargeRule(50, 90, 2.95);

    $this->assertEquals(2.95, $rule->getPrice());

    $this->assertEquals(90, $rule->getMaxPrice());

    $this->assertEquals(50, $rule->getMinPrice());

    $rule = new DeliveryChargeRule(null, 50, 4.95);

    $this->assertEquals(4.95, $rule->getPrice());

    $rule = new DeliveryChargeRule(null, 50, null);

    $this->assertEquals(0, $rule->getPrice());
  }

  public function test_handle_nullable_price_returns_string(): void
  {
    $rule = new DeliveryChargeRule(null, 50, null);

    $this->assertEquals("0", $rule->getPrice());
  }
  public function test_handle_nullable_price_returns_null(): void
  {
    $rule = new DeliveryChargeRule(null, 50, null);

    $this->assertNull($rule->getMinPrice());
  }


  /**
   * Basket
   */

  public function test_basket_adds_one_product(): void
  {
    $this->basket->add("R01");

    $this->assertEquals(1, $this->basket->countTotalItems());

    $this->basket->clear();
  }

  public function test_basket_throws_exception_for_non_existent_products(): void
  {
    $this->expectException(\Exception::class);

    $this->basket->add("LOW-QUALITY-PRODUCT");
  }

  public function test_basket_removes_one_product(): void
  {
    $this->basket->add("R01");

    $this->basket->remove("R01");

    $this->assertEquals(0, $this->basket->countTotalItems());
  }

  public function test_basket_counts_unique_items(): void
  {
    $this->basket->add("R01");
    $this->basket->add("R01");
    $this->basket->add("G01");

    $this->assertEquals(2, $this->basket->countUniqueItems());
  }

  public function test_basket_tracks_quantity_of_products(): void
  {
    $this->basket->add("R01");
    $this->basket->add("R01");
    $this->basket->add("R01");

    $this->assertEquals(3, $this->basket->countTotalItems());
  }

  public function test_basket_checks_if_product_has_offer(): void
  {
    $this->basket->add("R01");
    $this->basket->add("G01");

    $this->assertTrue($this->basket->productHasOffer("R01"));
    $this->assertFalse($this->basket->productHasOffer("G01"));
  }

  public function test_basket_gets_product_offer(): void
  {
    $this->basket->add("R01");
    $this->basket->add("G01");

    $this->assertNotNull($this->basket->getProductOffer("R01"));
    $this->assertNull($this->basket->getProductOffer("G01"));
  }

  public function test_basket_checks_if_product_is_in_basket(): void
  {
    $this->basket->add("R01");
    $this->basket->add("G01");

    $this->assertTrue($this->basket->productIsInBasket("R01"));
    $this->assertFalse($this->basket->productIsInBasket("LOW-QUALITY-PRODUCT"));
  }

  public function test_basket_checks_if_product_is_in_catalog(): void
  {
    $this->assertTrue($this->basket->productIsInCatalog("R01"));
    $this->assertFalse($this->basket->productIsInCatalog("LOW-QUALITY-PRODUCT"));
  }

  public function test_basket_calculates_delivery_costs(): void
  {
    $this->assertEquals(4.95, $this->basket->getDeliveryCost(0));
    $this->assertEquals(2.95, $this->basket->getDeliveryCost(50));
    $this->assertEquals(0.00, $this->basket->getDeliveryCost(90));
  }

  public function test_basket_returns_zero_if_there_are_no_products(): void
  {
    $this->assertEquals(0, $this->basket->getTotal());
  }

  public function test_basket_calculates_total(): void
  {

    /*
        | Products            | Total  |
        |---------------------|--------|
        | B01, G01            | $37.85 |
        | R01, R01            | $54.37 |
        | R01, G01            | $60.85 |
        | B01, B01, R01, R01, R01 | $98.27 |
        */

    $this->basket->add("B01");
    $this->basket->add("G01");

    $this->assertEquals(37.85, $this->basket->getTotal());
    $this->assertEquals(0, bccomp(37.85, $this->basket->getTotal(), 2));

    $this->basket->clear();

    $this->basket->add('R01');
    $this->basket->add('R01');

    $this->assertEquals(0, bccomp(54.37, $this->basket->getTotal(), 2));

    $this->basket->clear();

    $this->basket->add('R01');
    $this->basket->add('G01');

    $this->assertEquals(0, bccomp(60.85, $this->basket->getTotal(), 2));

    $this->basket->clear();

    $this->basket->add('B01');
    $this->basket->add('B01');
    $this->basket->add('R01');
    $this->basket->add('R01');
    $this->basket->add('R01');

    $this->assertEquals(0, bccomp(98.27, $this->basket->getTotal(), 2));
  }


  public function tearDown(): void
  {
    $this->basket->clear();
  }

  public function setUp(): void
  {

    $this->productCatalog = [
      'R01' => new CatalogProduct('R01', 'Red Widget', 32.95),
      'G01' => new CatalogProduct('G01', 'Green Widget', 24.95),
      'B01' => new CatalogProduct('B01', 'Blue Widget', 7.95),
    ];

    $this->deliveryChargeRules = [
      new DeliveryChargeRule(null, 50, 4.95),
      new DeliveryChargeRule(50, 90, 2.95),
      new DeliveryChargeRule(90, null, 0.00),
    ];

    $this->productOffers = [
      "R01" => new ProductOffer(
        "Buy one red widget, get the second at half price",
        "Limited time offer",
        [
          new QuantityCondition(2, null)
        ],
        [
          new FixedAdjustment("-16.475")
        ]
      )
    ];

    $this->basket = new Basket(
      $this->productCatalog,
      $this->deliveryChargeRules,
      $this->productOffers
    );
  }
}
