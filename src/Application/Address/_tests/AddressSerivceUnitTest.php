<?php
namespace Application\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use Application\Address\AddressService;
use Domain\Address\Address;
use Domain\Address\AddressRepositoryInterface;

class AddressServiceUnitTest extends TestCase {
  private $addressRepositoryMock;
  private $addressService;

  protected function setUp(): void {
    $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
    $this->addressService = new AddressService($this->addressRepositoryMock);
  }

  public function testCreateManyAddresses() {
    $customerId = 1;
  
    $addresses = [
      [
        'street' => 'Rua Durval Clemente',
        'number' => '1',
        'zipcode' => '02040-000'
      ]
    ];
  
    $expectedAddressObjects = [
      new Address(null, $customerId, 'Rua Durval Clemente', '1', '02040-000')
    ];
  
    $this->addressRepositoryMock->expects($this->once())
      ->method('createMany')
      ->with(
        $this->equalTo($customerId),
        $this->callback(function ($arg) use ($expectedAddressObjects) {
          if (!is_array($arg) || count($arg) !== 1 || !$arg[0] instanceof Address) 
            return false;
  
          return $arg[0]->getStreet() === 'Rua Durval Clemente' &&
                 $arg[0]->getNumber() === '1' &&
                 $arg[0]->getZipcode() === '02040-000' &&
                 $arg[0]->getCustomerId() === 1;
        })
      );
  
    $this->addressService->createMany($customerId, $addresses);
  }
  
  public function testUpdateManyAddresses() {
    $addresses = [
      new Address(1, 1, 'Rua Durval Clemente', '2', '02040-000')
    ];

    $this->addressRepositoryMock->expects($this->once())
      ->method('updateMany')
      ->with($addresses);

    $this->addressService->updateMany($addresses);
  }

  public function testRemoveAddress() { 
    $address = new Address(1, 1, 'Rua Durval Clemente', '1', '02040-000');
    $addressIds = [1];
  
    $this->addressRepositoryMock->expects($this->once())
      ->method('findById')
      ->with(1)
      ->willReturn($address);
  
    $this->addressRepositoryMock->expects($this->once())
      ->method('removeMany')
      ->with($this->equalTo($addressIds));
  
    $this->addressService->removeMany($addressIds);
  }
  
  public function testListAddressesByCustomerId() {
    $this->addressRepositoryMock->expects($this->once())
      ->method('findByCustomerId')
      ->with(1)
      ->willReturn([
        new Address(1, 1, 'Rua Durval Clemente', '1', '02040-000')
      ]);

    $addresses = $this->addressService->listByCustomerId(1);
    
    $this->assertCount(1, $addresses);
    $this->assertEquals('Rua Durval Clemente', $addresses[0]->getStreet());
  }
}
