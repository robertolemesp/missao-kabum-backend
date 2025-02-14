<?php
namespace Infrastructure\Repository\Address;

use Domain\Address\Address;
use Domain\Address\AddressRepositoryInterface;
use PDO;

class MySQLAddressRepository implements AddressRepositoryInterface {
  private $pdo;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  public function create(Address $address): void {
    $stmt = $this->pdo->prepare("INSERT INTO customer_address (customer_id, street, number, zipcode) VALUES (:customer_id, :street, :number, :zipcode)");
    $stmt->execute([
      'customer_id' => $address->getCustomerId(),
      'street' => $address->getStreet(),
      'number' => $address->getNumber(),
      'zipcode' => $address->getZipcode(),
    ]);
  }

  public function createMany(int $customerId, array $addresses): void {
    if (empty($addresses)) 
      return;

    $placeholders = [];
    $values = [];
  
    foreach ($addresses as $address) {
      if (!$address instanceof Address) 
        throw new \InvalidArgumentException("All elements in the array must be instances of Address.");
      
      $placeholders[] = '(?, ?, ?, ?)';
      $values[] = $customerId;
      $values[] = $address->getStreet();
      $values[] = $address->getNumber();
      $values[] = $address->getZipcode();
    }

    $sql = "INSERT INTO customer_address (customer_id, street, number, zipcode) VALUES " . implode(', ', $placeholders);
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($values);
  }


  public function findById(int $id): ?Address {
    $stmt = $this->pdo->prepare("SELECT * FROM customer_address WHERE id = ?");
    $stmt->execute([$id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) 
      return null;

    return new Address(
      $row['id'],
      $row['customer_id'],
      $row['street'],
      $row['number'],
      $row['zipcode']
    );
  }

  public function findByCustomerId(int $customerId): array {
    $stmt = $this->pdo->prepare("SELECT * FROM customer_address WHERE customer_id = ?");
    $stmt->execute([$customerId]);

    $addresses = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $addresses[] = new Address(
        $row['id'],
        $row['customer_id'],
        $row['street'],
        $row['number'],
        $row['zipcode']
      );
    }
    return $addresses;
  }

  public function updateMany(array $addresses): void {
    $stmt = $this->pdo->prepare("UPDATE customer_address SET street = :street, number = :number, zipcode = :zipcode WHERE id = :id");
  
    foreach ($addresses as $address) {
      if ($address instanceof Address) {
        $stmt->execute([
          'id' => $address->getId(),
          'street' => $address->getStreet(),
          'number' => $address->getNumber(),
          'zipcode' => $address->getZipcode(),
        ]);
      }
    }
  }
  
  public function remove(int $id): void {
    $stmt = $this->pdo->prepare("DELETE FROM customer_address WHERE id = ?");
    $stmt->execute([$id]);
  }

  public function removeMany(array $addressIds): void {
    if (empty($addressIds)) 
      return;
  
    $placeholders = implode(',', array_fill(0, count($addressIds), '?'));
    
    $stmt = $this->pdo->prepare("DELETE FROM customer_address WHERE id IN ($placeholders)");
    $stmt->execute($addressIds);
  }

  public function removeAllByCustomerId(int $customerId): void {
    $stmt = $this->pdo->prepare("DELETE FROM customer_address WHERE customer_id = ?");
    $stmt->execute([$customerId]);
  }
}
