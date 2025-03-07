<?php

namespace Application\Tests;

use PHPUnit\Framework\TestCase;

use Infrastructure\DependencyInjection\DependencyInjectionContainer;

use Domain\Address\Address;
use Application\Address\AddressService;
use Infrastructure\Repository\Address\MySQLAddressRepository;

use Infrastructure\Database\Connection\DatabaseConnection;
use Infrastructure\Database\Setup\DatabaseSetup;

class AddressServiceIntegrationTest extends TestCase {
  private $dbConnection;
  private $container;
  private $addressService;
  private $customerService;
 
  protected function setUp(): void {
    $this->dbConnection = DatabaseConnection::getConnection();
    
    $this->container = new DependencyInjectionContainer($this->dbConnection);

    $this->addressService = $this->container->getAddressService();
    $this->customerService = $this->container->getCustomerService();
  
    DatabaseSetup::clearTestDatabase();
  }

  protected function tearDown(): void {
    parent::tearDown();
    DatabaseSetup::clearTestDatabase();
  }

  public function testCreateAddress() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '12.345.678-9',
      'phone' => '1234567890'
    ];
  
    $this->customerService->create($customerData);
  
    $customers = $this->customerService->list();
    $this->assertNotEmpty($customers);
  
    $customerId = $customers[0]['id'];
  
    $addresses = [
      [
        'customerId' => $customerId,
        'street' => 'Rua Durval Clemente',
        'number' => '1A',
        'zipcode' => '12345-678',
        'city' => 'São Paulo',
        'state' => 'SP'
      ]
    ];
  
    $this->addressService->createMany($customerId, $addresses);
  
    $retrievedAddresses = $this->addressService->listByCustomerId($customerId);
    $this->assertNotEmpty($retrievedAddresses);
    $this->assertEquals('Rua Durval Clemente', $retrievedAddresses[0]->getStreet());
    $this->assertEquals('1A', $retrievedAddresses[0]->getNumber());
    $this->assertEquals('12345-678', $retrievedAddresses[0]->getZipcode());
    $this->assertEquals('São Paulo', $retrievedAddresses[0]->getCity());
    $this->assertEquals('SP', $retrievedAddresses[0]->getState());
  }
  
  public function testUpdateAddress() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '12.345.678-9',
      'phone' => '1234567890'
    ];
  
    $this->customerService->create($customerData);
  
    $customers = $this->customerService->list();
    $this->assertNotEmpty($customers);
  
    $customerId = $customers[0]['id'];
  
    $addresses = [
      new Address(null, $customerId, 'Rua Durval Clemente', '1', '12345-678', 'São Paulo', 'SP')
    ];
  
    $this->addressService->createMany($customerId, $addresses);
  
    $retrievedAddresses = $this->addressService->listByCustomerId($customerId);
    $this->assertNotEmpty($retrievedAddresses);
  
    $addressToUpdate = $retrievedAddresses[0];
    $updatedAddress = new Address(
      $addressToUpdate->getId(),
      $customerId,
      'Rua Atualizada',
      '2B',
      '02041-000',
      'Rio de Janeiro',
      'RJ'
    );
  
    $this->addressService->updateMany([$updatedAddress]);
  
    $retrievedAddresses = $this->addressService->listByCustomerId($customerId);
    $this->assertEquals('Rua Atualizada', $retrievedAddresses[0]->getStreet());
    $this->assertEquals('2B', $retrievedAddresses[0]->getNumber());
    $this->assertEquals('02041-000', $retrievedAddresses[0]->getZipcode());
    $this->assertEquals('Rio de Janeiro', $retrievedAddresses[0]->getCity());
    $this->assertEquals('RJ', $retrievedAddresses[0]->getState());
  }

  public function testRemoveAddress() {
    $this->customerService->create([
      'name' => 'Roberto Lemes',
      'email' => 'roberto@missao-kabum.com',
      'password' => 'AlmostSecurePass123!',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.578-91',
      'rg' => '12.345.678-9',
      'phone' => '123456789',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '02040-000',
          'city' => 'São Paulo',
          'state' => 'SP'
        ]
      ]
    ]);
  
    $customers = $this->customerService->list();
    $this->assertNotEmpty($customers);
    
    $customerId = $customers[0]['id'];
  
    $addresses = [
      new Address(null, $customerId, 'Rua Durval Clemente', '1', '12345-678', 'São Paulo', 'SP')
    ];
  
    $this->addressService->createMany($customerId, $addresses);
  
    $retrievedAddresses = $this->addressService->listByCustomerId($customerId);
    $this->assertNotEmpty($retrievedAddresses);
  
    $addressIds = array_map(fn($address) => $address->getId(), $retrievedAddresses);
  
    $this->addressService->removeMany($addressIds);
  
    $retrievedAddresses = $this->addressService->listByCustomerId($customerId);
    $this->assertEmpty($retrievedAddresses);
  }

  public function testAddressValidation() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Street is required.');

    new Address(null, 1, '', '1', '12345-678', 'São Paulo', 'SP');
  }

  public function testInvalidZipcodeFormat() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid postal code format. Expected format: #####-###');

    new Address(null, 1, 'Rua Teste', '1', '1234', 'São Paulo', 'SP');
  }

  public function testCityIsRequired() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('City is required.');

    new Address(null, 1, 'Rua Teste', '1', '12345-678', '', 'SP');
  }

  public function testStateIsRequired() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('State is required.');

    new Address(null, 1, 'Rua Teste', '1', '12345-678', 'São Paulo', '');
  }

  public function testNumberIsRequired() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Number is required.');

    new Address(null, 1, 'Rua Teste', '', '12345-678', 'São Paulo', 'SP');
  }
}
