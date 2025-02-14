<?php
use PHPUnit\Framework\TestCase;
use Domain\Address\Address;
use InvalidArgumentException;

class AddressTest extends TestCase {
  public function testValidAddress() {
    $address = new Address(1, 100, 'Rua Durval Clemente', '1', '00000-000');

    $this->assertEquals(1, $address->getId());
    $this->assertEquals(100, $address->getCustomerId());
    $this->assertEquals('Rua Durval Clemente', $address->getStreet());
    $this->assertEquals('1', $address->getNumber());
    $this->assertEquals('00000-000', $address->getZipcode());
  }

  public function testEmptyStreetThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Street, number, and zipcode cannot be empty');

    new Address(1, 100, '', '1', '00000-000');
  }

  public function testEmptyNumberThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Street, number, and zipcode cannot be empty');

    new Address(1, 100, 'Rua Durval Clemente', '', '00000-000');
  }

  public function testEmptyZipcodeThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Street, number, and zipcode cannot be empty');

    new Address(1, 100, 'Rua Durval Clemente', '1', '');
  }

  public function testInvalidZipcodeFormatThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid zipcode format');

    new Address(1, 100, 'Rua Durval Clemente', '1', '00000000');
  }

  public function testInvalidZipcodeWithLettersThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid zipcode format');

    new Address(1, 100, 'Rua Durval Clemente', '1', '12A45-678');
  }
}
