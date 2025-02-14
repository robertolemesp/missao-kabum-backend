<?php
namespace Infrastructure\Repository\Customer;

use Domain\Customer\Customer;
use Domain\Customer\CustomerRepositoryInterface;
use PDO;

class MySQLCustomerRepository implements CustomerRepositoryInterface {
  private $pdo;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  public function create(Customer $customer): ?int {
    $stmt = $this->pdo->prepare(
      "INSERT INTO customer (name, email, password, birthday, cpf, rg, phone) 
      VALUES (:name, :email, :password, :birthday, :cpf, :rg, :phone)"
    );

    $success = $stmt->execute([
      'name' => $customer->getName(),
      'email' => $customer->getEmail(),
      'password' => $customer->getPassword(),
      'birthday' => $customer->getBirthday()->format('Y-m-d'),
      'cpf' => $customer->getCpf(),
      'rg' => $customer->getRg(),
      'phone' => $customer->getPhone(),
    ]);

    return $success ? (int) $this->pdo->lastInsertId() : null;
  }

  public function update(Customer $customer): void {
    $stmt = $this->pdo->prepare(
      "UPDATE customer SET name = :name, password = :password, birthday = :birthday, cpf = :cpf, rg = :rg, phone = :phone WHERE id = :id"
    );

    $stmt->execute([
      'id' => $customer->getId(),
      'name' => $customer->getName(),
      'password' => $customer->getPassword(),
      'birthday' => $customer->getBirthday()->format('Y-m-d'),
      'cpf' => $customer->getCpf(),
      'rg' => $customer->getRg(),
      'phone' => $customer->getPhone(),
    ]);
  }

  public function findById(int $id): ?Customer {
    $stmt = $this->pdo->prepare("SELECT * FROM customer WHERE id = ?");
    $stmt->execute([$id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) 
      return null;
    
    return new Customer(
      $row['id'],
      $row['name'],
      $row['email'],
      $row['password'],
      new \DateTime($row['birthday']),
      $row['cpf'],
      $row['rg'],
      $row['phone']
    );
  }

  public function findByEmail(string $email): ?Customer {
    $stmt = $this->pdo->prepare("SELECT * FROM customer WHERE email = ?");
    $stmt->execute([$email]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      return new Customer(
        $row['id'],
        $row['name'],
        $row['email'],
        $row['password'],
        new \DateTime($row['birthday']),
        $row['cpf'],
        $row['rg'],
        $row['phone']
      );
    }
  
    return null;
  }

  public function remove(int $id): void {
    $stmt = $this->pdo->prepare("DELETE FROM customer WHERE id = ?");
    $stmt->execute([$id]);
  }

  public function list(): array {
    $stmt = $this->pdo->query("SELECT * FROM customer");

    $customers = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $customers[] = new Customer(
        $row['id'],
        $row['name'],
        $row['email'],
        $row['password'],
        new \DateTime($row['birthday']),
        $row['cpf'],
        $row['rg'],
        $row['phone']
      );
    }

    return $customers;
  }  
}
