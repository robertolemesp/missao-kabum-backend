<?php
namespace Application\Address;

use Domain\Address\Address;
use Domain\Address\AddressServiceInterface;
use Domain\Address\AddressRepositoryInterface;

class AddressService implements AddressServiceInterface {
  private $addressRepository;

  public function __construct(AddressRepositoryInterface $addressRepository) {
    $this->addressRepository = $addressRepository;
  }

  public function createMany(int $customerId, array $addresses): void {
    if (empty($addresses)) 
      return;

    $addressObjects = array_map(function($addressData) use ($customerId) {
      if ($addressData instanceof Address)
        return $addressData;

      if (!is_array($addressData)) 
        throw new \InvalidArgumentException("Invalid address format.");
          
      return new Address(
        null,
        $customerId,
        $addressData['street'],
        $addressData['number'],
        $addressData['zipcode']
      );
    }, $addresses);

    $this->addressRepository->createMany($customerId, $addressObjects);
  }

  public function listByCustomerId($customerId): array {
    return $this->addressRepository->findByCustomerId($customerId);
  }

  public function updateMany(array $addresses): void {
    if (empty($addresses)) 
      return;
    
    foreach ($addresses as $address) {
      if (!$address instanceof Address) 
        throw new \InvalidArgumentException("All elements in the array must be instances of Address.");
    }

    $this->addressRepository->updateMany($addresses);
  }


  public function removeAllByCustomerId($customerId): void {
    $addresses = $this->addressRepository->findByCustomerId($customerId);
    
    foreach ($addresses as $address) 
      $this->addressRepository->remove($address->getId());
  }

  public function remove($id): void {
    $existingAddress = $this->addressRepository->findById($id);

    if (!$existingAddress) 
      throw new \InvalidArgumentException("Address not found.");
  
    $this->addressRepository->remove($id);
  }

  public function removeMany(array $addressIds): void {
    if (empty($addressIds)) 
      return;
  
    $addresses = array_filter(array_map(
      fn($id) => $this->addressRepository->findById($id), $addressIds)
    );
  
    if (count($addresses) !== count($addressIds)) 
      throw new \InvalidArgumentException("One or more addresses not found.");
  
    $this->addressRepository->removeMany($addressIds);
  }
}
