<?php
namespace Application\Address;

use Domain\Address\Address;
use Domain\Address\AddressRepositoryInterface;

class AddressService implements AddressServiceInterface {
  public function __construct(private AddressRepositoryInterface $addressRepository) {}

  public function createMany(int $customerId, array $addresses): ?array {
    if (empty($addresses)) 
      return null;

    $addressObjects = $this->mapAddresses($customerId, $addresses);

    return $this->addressRepository->createMany($customerId, $addressObjects);
  }

  public function listByCustomerId(int $customerId): array {
    return $this->addressRepository->findByCustomerId($customerId) ?? [];
  }

  public function updateMany(array $addresses): void {
    if (empty($addresses)) 
      return;

    foreach ($addresses as $address) 
      if (!$address instanceof Address) 
        throw new \InvalidArgumentException("All elements must be Address instances.");

    $this->addressRepository->updateMany($addresses);
  }

  public function removeAllByCustomerId(int $customerId): void {
    $addresses = $this->addressRepository->findByCustomerId($customerId);

    $this->addressRepository->removeMany(
      array_map(
        fn(Address $address) => $address->getId(), 
        $addresses
      )
    );
  }

  public function remove(int $id): void {
    if (!$this->addressRepository->findById($id)) 
      throw new \InvalidArgumentException("Address not found.");

    $this->addressRepository->remove($id);
  }

  public function removeMany(array $addressIds): void {
    if (empty($addressIds)) 
      return;

    $existingAddresses = array_filter(
      array_map(fn($id) => $this->addressRepository->findById($id), $addressIds)
    );

    if (count($existingAddresses) !== count($addressIds)) 
      throw new \InvalidArgumentException("One or more addresses not found.");
    
    $this->addressRepository->removeMany($addressIds);
  }

  public function mapToArray(Address $address): array {
    return $this->mapAddressToArray($address);
  }

  public function mapAddressToArray(Address $address): array {
    if (!$address instanceof Address) 
      throw new \InvalidArgumentException("Expected Address instance in mapAddressToArray()");

    return [
      'id' => $address->getId(),
      'customerId' => $address->getCustomerId(),
      'street' => $address->getStreet(),
      'number' => $address->getNumber(),
      'city' => $address->getCity(),
      'zipcode' => $address->getZipcode(),
      'state' => $address->getState()
    ];
  }

  public function mapAddresses(int $customerId, array $addresses): array {
    return array_map(fn($addr) => $addr instanceof Address
      ? $addr
      : new Address(
          $addr['id'] ?? null,
          $customerId,
          $addr['street'],
          $addr['number'],
          $addr['zipcode'],
          $addr['city'],
          $addr['state']
      ), $addresses);
  }
}
