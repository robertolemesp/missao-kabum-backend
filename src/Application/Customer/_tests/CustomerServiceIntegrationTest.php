<?php
namespace Application\Tests;

use PHPUnit\Framework\TestCase;

use Infrastructure\DependencyInjection\DependencyInjectionContainer;

use Infrastructure\Database\Config\DatabaseConfig;
use Infrastructure\Database\Connection\DatabaseConnection;
use Infrastructure\Database\Setup\DatabaseSetup;

use Application\Address\AddressService;
use Infrastructure\Repository\Address\MySQLAddressRepository;

use PDO;

class CustomerServiceIntegrationTest extends TestCase {
  private $customerService;
  private $dbConnection;

  protected function setUp(): void {
    $this->dbConnection = DatabaseConnection::getConnection();

    $this->container = new DependencyInjectionContainer($this->dbConnection);
  
    $this->customerService = $this->container->getCustomerService();

    $this->addressService = new AddressService(new MySQLAddressRepository($this->dbConnection));
  
    DatabaseSetup::reset();
  }
  
  public function testCreateCustomerWithAddresses() {
    $creatingCustomer = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' =>  '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '02040-000'
        ]
      ]
    ];
  
    $this->customerService->create($creatingCustomer);
  
    $customer = $this->customerService->list()[0];
    $this->assertNotNull($customer);
    $this->assertEquals('Roberto Lemes', $customer->getName());
  
    $addresses = $this->addressService->listByCustomerId($customer->getId());
  
    $this->assertNotEmpty($addresses);
    $this->assertEquals('Rua Durval Clemente', $addresses[0]->getStreet());
  }
  

  public function testUpdateCustomerWithAddresses() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '02040-000'
        ]
      ]
    ];

    $this->customerService->create($customerData);

    $updatedCustomerData = [
      'id' => 1,
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => [
        [
          'id' => 1,
          'street' => 'Rua Durval Clemente',
          'number' => '2',
          'zipcode' => '02040-000'
        ]
      ]
    ];

    $this->customerService->update($updatedCustomerData);

    $customer = $this->customerService->list()[0];

    $this->assertNotNull($customer);
    $this->assertEquals('Roberto Lemes', $customer->getName());

    $addresses = $this->addressService->listByCustomerId($customer->getId());

    $this->assertNotEmpty($addresses);
    $this->assertEquals('Rua Durval Clemente', $addresses[0]->getStreet());
    $this->assertEquals('2', $addresses[0]->getNumber());
  }

  public function testRemoveCustomer() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securePassword123',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '02040-000'
        ]
      ]
    ];
  
    $this->customerService->create($customerData);
  
    $customers = $this->customerService->list();
    $this->assertNotEmpty($customers);
    $customerId = $customers[0]->getId();
  
    $this->customerService->remove($customerId);
  
    $customers = $this->customerService->list();
    $this->assertEmpty($customers);
  } 

  public function testListCustomers() {
    $customer = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@missao-kabum.com',
      'password' => 'AlmostSecurePass123!',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '02040-000'
        ]
      ]
    ];

    $customer2 = [
      'name' => 'Roberto Lemes Padilha',
      'email' => 'roberto2@missao-kabum.com',
      'password' => 'AnotherAlmostSecurePass123!',
      'birthday' => '1995-01-11',
      'cpf' => '987.654.321-00',
      'rg' => '0987654321',
      'phone' => '0987654321',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '02040-000'
        ]
      ]
    ];

    $this->customerService->create($customer);
    $this->customerService->create($customer2);

    $customers = $this->customerService->list();

    $this->assertCount(2, $customers);
    $this->assertEquals('Roberto Lemes', $customers[0]->getName());
    $this->assertEquals('Roberto Lemes Padilha', $customers[1]->getName());
  }
}
