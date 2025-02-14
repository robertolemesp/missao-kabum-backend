<?php
namespace Application\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use Domain\Customer\Customer;
use Application\Customer\CustomerService;
use Domain\Customer\CustomerRepositoryInterface;

use Domain\Address\Address;
use Application\Address\AddressService;

class CustomerServiceUnitTest extends TestCase {
  private $customerRepositoryMock;
  private $addressServiceMock;
  private $customerService;

  protected function setUp(): void {
    $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
    $this->addressServiceMock = $this->createMock(AddressService::class);
    $this->customerService = new CustomerService($this->customerRepositoryMock, $this->addressServiceMock);
  }

  public function testCreateCustomer() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securepassword',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => []
    ];
  
    $this->customerRepositoryMock->expects($this->once())
      ->method('create')
      ->with($this->isInstanceOf(Customer::class))
      ->willReturn(1);
  
    $this->addressServiceMock->expects($this->never())
      ->method('createMany');
  
    $this->customerService->create($customerData);
  }
  

  public function testUpdateCustomer() {
    $customerData = [
      'id' => 1,
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securepassword',
      'birthday' => '1980-05-10',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => []
    ];

    $customer = new Customer(
      $customerData['id'], 
      $customerData['name'], 
      $customerData['email'], 
      $customerData['password'], 
      new \DateTime($customerData['birthday']), 
      $customerData['cpf'], 
      $customerData['rg'], 
      $customerData['phone']
    );

    $this->customerRepositoryMock->expects($this->once())
      ->method('findById')
      ->with(1)
      ->willReturn($customer);

    $this->customerRepositoryMock->expects($this->once())
      ->method('update')
      ->with($this->isInstanceOf(Customer::class));

    $this->addressServiceMock->expects($this->never())
      ->method('updateMany');

    $this->customerService->update($customerData);
  }

  public function testDeleteCustomer() {
    $this->customerRepositoryMock->expects($this->once())
      ->method('remove')
      ->with(1);

    $this->customerService->remove(1);
  }

  public function testListCustomers() {
    $this->customerRepositoryMock->expects($this->once())
      ->method('list')
      ->willReturn([]);

    $this->customerService->list();
  }

  public function testCreateCustomerWithAddresses() {
    $customerData = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@example.com',
      'password' => 'securepassword',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-01',
      'rg' => '1234567890',
      'phone' => '1234567890',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '00000-000'
        ]
      ]
    ];
  
    $this->customerRepositoryMock->expects($this->once())
      ->method('create')
      ->with($this->isInstanceOf(Customer::class))
      ->willReturn(1);
  
    $expectedAddresses = [
      new Address(null, 1, 'Rua Durval Clemente', '1', '00000-000')
    ];
  
    $this->addressServiceMock->expects($this->once())
      ->method('createMany')
      ->with(
        $this->equalTo(1),
        $this->callback(function ($addresses) use ($expectedAddresses) {
          return is_array($addresses) && count($addresses) === 1 && $addresses[0] instanceof Address &&
                 $addresses[0]->getStreet() === 'Rua Durval Clemente' &&
                 $addresses[0]->getNumber() === '1' &&
                 $addresses[0]->getZipcode() === '00000-000' &&
                 $addresses[0]->getCustomerId() === 1;
        })
      );
  
    $this->customerService->create($customerData);
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
}
