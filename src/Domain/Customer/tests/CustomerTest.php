<?php
use PHPUnit\Framework\TestCase;
use Domain\Customer\Customer;
use InvalidArgumentException;
use DateTime;

class CustomerTest extends TestCase {
  public function testValidCustomer() {
    $customer = new Customer(
      1,
      'Roberto Lemes',
      'roberto@example.com',
      'securepassword',
      new DateTime('1995-01-11'),
      '123.456.789-01',
      '1234567890',
      '1234567890'
    );

    $this->assertEquals(1, $customer->getId());
    $this->assertEquals('Roberto Lemes', $customer->getName());
    $this->assertEquals('roberto@example.com', $customer->getEmail());
    $this->assertTrue(password_verify('securepassword', $customer->getPassword()));
    $this->assertEquals(new DateTime('1995-01-11'), $customer->getBirthday());
    $this->assertEquals('123.456.789-01', $customer->getCpf());
    $this->assertEquals('1234567890', $customer->getRg());
    $this->assertEquals('1234567890', $customer->getPhone());
  }

  public function testEmptyNameThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Name is required.');

    new Customer(null, '', 'roberto@example.com', 'securepassword', new DateTime('1995-01-11'), '123.456.789-01', '1234567890', '1234567890');
  }

  public function testInvalidEmailThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid email format.');

    new Customer(null, 'Roberto Lemes', 'invalid-email', 'securepassword', new DateTime('1995-01-11'), '123.456.789-01', '1234567890', '1234567890');
  }

  public function testShortPasswordThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Password must be at least 8 characters long.');

    new Customer(null, 'Roberto Lemes', 'roberto@example.com', 'short', new DateTime('1995-01-11'), '123.456.789-01', '1234567890', '1234567890');
  }

  public function testInvalidCpfThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid CPF format. Expected: ###.###.###-##.');

    new Customer(null, 'Roberto Lemes', 'roberto@example.com', 'securepassword', new DateTime('1995-01-11'), '12345678901', '1234567890', '1234567890');
  }

  public function testEmptyRgThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('RG is required.');

    new Customer(null, 'Roberto Lemes', 'roberto@example.com', 'securepassword', new DateTime('1995-01-11'), '123.456.789-01', '', '1234567890');
  }

  public function testEmptyPhoneThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Phone number is required.');

    new Customer(null, 'Roberto Lemes', 'roberto@example.com', 'securepassword', new DateTime('1995-01-11'), '123.456.789-01', '1234567890', '');
  }

  public function testFutureBirthdayThrowsException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Birthday cannot be in the future.');

    new Customer(null, 'Roberto Lemes', 'roberto@example.com', 'securepassword', new DateTime('2995-01-11'), '123.456.789-01', '1234567890', '1234567890');
  }
}
