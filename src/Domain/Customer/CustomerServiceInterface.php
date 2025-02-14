<?php
namespace Domain\Customer;

interface CustomerServiceInterface {
  public function create($creatingCustomer): void;
  public function update(array $updatingCustomer): void;
  public function remove(int $id): void;
  
  /**
   * @return Customer[]
   */
  public function list(): array;

  public function exists(string $email): bool;
}
