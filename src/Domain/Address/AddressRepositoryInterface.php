<?php
namespace Domain\Address;

interface AddressRepositoryInterface {
  public function create(Address $address): void;
  public function createMany(int $customerId, array $addresses): void;
  public function findById(int $id): ?Address;
  public function findByCustomerId(int $customerId): array;
  public function updateMany(array $address): void;
  public function remove(int $id): void;
  public function removeMany(array $addressIds): void;
  public function removeAllByCustomerId(int $customerId): void;
}
