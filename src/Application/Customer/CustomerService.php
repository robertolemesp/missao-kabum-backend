<?php
namespace Application\Customer;

use Domain\Customer\Customer;
use Domain\Customer\CustomerServiceInterface;
use Domain\Customer\CustomerRepositoryInterface;

use Domain\Address\Address;
use Application\Address\AddressService;

class CustomerService implements CustomerServiceInterface {
  private $customerRepository;
  private $addressService;

  public function __construct(CustomerRepositoryInterface $customerRepository, AddressService $addressService) {
    $this->customerRepository = $customerRepository;
    $this->addressService = $addressService;
  }

  public function create($creatingCustomer): void {
    $customer = new Customer(
      null,
      $creatingCustomer['name'],
      $creatingCustomer['email'],
      $creatingCustomer['password'],
      new \DateTime($creatingCustomer['birthday']),
      $creatingCustomer['cpf'],
      $creatingCustomer['rg'],
      $creatingCustomer['phone']
    );
  
    $customerId = $this->customerRepository->create($customer);
    $customer->setId($customerId);
  
    if (!isset($creatingCustomer['addresses']) || empty($creatingCustomer['addresses'])) 
      return;
  
    $addresses = array_map(function ($addressData) use ($customer) {
      return new Address(
        null,
        $customer->getId(),
        $addressData['street'],
        $addressData['number'],
        $addressData['zipcode']
      );
    }, $creatingCustomer['addresses']);
  
    $this->addressService->createMany($customer->getId(), $addresses);
  }
  

  public function update(array $updatingCustomer): void {
    $foundCustomer = $this->customerRepository->findById($updatingCustomer['id']);
  
    if (!$foundCustomer) 
      throw new \InvalidArgumentException("Customer not found.");
  
    $customer = new Customer(
      $foundCustomer->getId(),
      $updatingCustomer['name'],
      $foundCustomer->getEmail(),
      $updatingCustomer['password'],
      new \DateTime($updatingCustomer['birthday']),
      $updatingCustomer['cpf'],
      $updatingCustomer['rg'],
      $updatingCustomer['phone']
    );
  
    $this->customerRepository->update($customer);
  
    if (empty($updatingCustomer['addresses'])) 
      return;
  
    $addresses = array_map(function ($addressData) use ($customer) {
      if ($addressData instanceof Address) {
        return new Address(
          $addressData->getId(),
          $customer->getId(),
          $addressData->getStreet(),
          $addressData->getNumber(),
          $addressData->getZipcode()
        );
      }
  
      return new Address(
        $addressData['id'] ?? null, 
        $customer->getId(),
        $addressData['street'],
        $addressData['number'],
        $addressData['zipcode']
      );
    }, $updatingCustomer['addresses']);
  
    $this->addressService->updateMany($addresses);
  }

  public function remove($id): void {
    $this->addressService->removeAllByCustomerId($id);
    
    $this->customerRepository->remove($id);
  }


  public function list(): array {
    $customers = $this->customerRepository->list();
  
    return array_map(function($customer) {
      $addresses = $this->addressService->listByCustomerId($customer->getId());
  
      return new Customer(
        $customer->getId(),
        $customer->getName(),
        $customer->getEmail(),
        $customer->getPassword(),
        $customer->getBirthday(),
        $customer->getCpf(),
        $customer->getRg(),
        $customer->getPhone(),
        $addresses
      );
    }, $customers);
  }
  

  public function exists($email): bool {
    return $this->customerRepository->findByEmail($email) !== null;
  }
}

