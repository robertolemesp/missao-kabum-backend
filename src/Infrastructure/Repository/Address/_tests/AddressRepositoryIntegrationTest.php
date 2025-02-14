<?php
namespace Domain\Address\Tests;

use PDO;

use PHPUnit\Framework\TestCase;

use Domain\Address\Address;
use Infrastructure\Database\Config\DatabaseConfig;
use Infrastructure\Repository\MySQLAddressRepository;

class AddressRepositoryIntegrationTest extends TestCase {
  private $pdo;
  private $addressRepository;

  protected function setUp(): void {
    global $pdo;
    
    $this->pdo = $pdo;
    
    $this->pdo->exec("DELETE FROM customer_address");

    $this->addressRepository = new MySQLAddressRepository($this->pdo);
  }

  public function testCreateAddress() {
    $address = new Address(null, 1, 'Rua Durval Clemente', '10', '00000-000');
    $this->addressRepository->create($address);

    $savedAddresses = $this->addressRepository->findByCustomerId(1);
    $this->assertCount(1, $savedAddresses);
    $this->assertEquals('Rua Teste', $savedAddresses[0]->getStreet());
  }

  public function testFindAddressesByCustomerId() {
    $address = new Address(null, 1, 'Rua Teste', '20', '11111-111');
    $this->addressRepository->create($address);

    $addresses = $this->addressRepository->findByCustomerId(1);
    $this->assertNotEmpty($addresses);
    $this->assertEquals('Rua Teste', $addresses[0]->getStreet());
  }

  public function testDeleteAllByCustomerId() {
    $address1 = new Address(null, 1, 'Rua 1', '10', '00000-000');
    $address2 = new Address(null, 1, 'Rua 2', '20', '11111-111');
    $this->addressRepository->create($address1);
    $this->addressRepository->create($address2);

    $addresses = $this->addressRepository->findByCustomerId(1);
    $this->assertCount(2, $addresses);

    $this->addressRepository->deleteAllByCustomerId(1);

    $addressesAfterDeletion = $this->addressRepository->findByCustomerId(1);
    $this->assertCount(0, $addressesAfterDeletion);
  }
}
