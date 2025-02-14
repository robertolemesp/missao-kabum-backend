<?php
namespace Domain\Customer\Tests;

use PDO;

use PHPUnit\Framework\TestCase;

use Domain\Customer\Customer;
use Infrastructure\Database\Config\DatabaseConfig;
use Infrastructure\Repository\MySQLCustomerRepository;

class CustomerRepositoryIntegrationTest extends TestCase {
  private $pdo;
  private $customerRepository;

  protected function setUp(): void {
    global $pdo;
    
    $this->pdo = $pdo;
    
    $this->pdo->exec("DELETE FROM customer");

    $this->customerRepository = new MySQLCustomerRepository($this->pdo);
  }

  public function testCreateCustomer() {
    $customer = new Customer(null, 'Roberto Lemes', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $this->customerRepository->create($customer);

    $retrievedCustomer = $this->customerRepository->findById(1);

    $this->assertInstanceOf(Customer::class, $retrievedCustomer);
    $this->assertEquals('Roberto Lemes', $retrievedCustomer->getName());
  }

  public function testEditCustomer() {
    $customer = new Customer(null, 'Roberto Lemes', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $this->customerRepository->create($customer);

    $customer = $this->customerRepository->findById(1);
    $customer->setName('Roberto Lemes Updated');
    $this->customerRepository->update($customer);

    $updatedCustomer = $this->customerRepository->findById(1);
    $this->assertEquals('Roberto Lemes Updated', $updatedCustomer->getName());
  }

  public function testDeleteCustomer() {
    $customer = new Customer(null, 'Roberto Lemes', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $this->customerRepository->create($customer);

    $this->customerRepository->delete(1);
    $deletedCustomer = $this->customerRepository->findById(1);

    $this->assertNull($deletedCustomer);
  }

  public function testListCustomers() {
    $customer1 = new Customer(null, 'Roberto Lemes', new \DateTime('1995-11-01'), '12345678901', '1234567890', '1234567890');
    $customer2 = new Customer(null, 'Roberto Lemes Padilha', new \DateTime('1990-05-10'), '12345678902', '1234567891', '1234567891');

    $this->customerRepository->create($customer1);
    $this->customerRepository->create($customer2);

    $customers = $this->customerRepository->list();

    $this->assertCount(2, $customers);
    $this->assertInstanceOf(Customer::class, $customers[0]);
    $this->assertInstanceOf(Customer::class, $customers[1]);
  }
}
