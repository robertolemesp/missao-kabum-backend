<?php
namespace Domain\Address;

interface AddressServiceInterface {
  public function createMany(int $customerId, array $addresses): void;
  public function listByCustomerId($customerId): array;
  public function updateMany(array $addresses): void;
  public function removeAllByCustomerId($customerId): void;
  public function removeMany(array $addressIds): void;
}
