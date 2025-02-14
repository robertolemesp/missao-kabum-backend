<?php
namespace Infrastructure\Database\Seeder;

use Application\Customer\CustomerService;

use Infrastructure\Repository\Customer\MySQLCustomerRepository;

use Infrastructure\Logging\LogService;

class TestUserSeeder {
  private $customerService;

  public function __construct(CustomerService $customerService) {
    $this->customerService = $customerService;
  }

  public function seed() {
    $testUserData = [
      'name' => 'Test User',
      'email' => 'test_user@seeding.com',
      'password' => 'AlmostSecurePass123!',
      'birthday' => '1995-01-11',
      'cpf' => '000.000.000-00',
      'rg' => '123456789',
      'phone' => '1234567890',
      'addresses' => [
        [
          'street' => 'Rua do Seed 1',
          'number' => '101',
          'zipcode' => '12345-678'
        ],
        [
          'street' => 'Rua do Seed + 1',
          'number' => '101 + 1',
          'zipcode' => '98765-432'
        ]
      ]
    ];
    
    if (!$this->customerService->exists($testUserData['email'])) {
      $this->customerService->create($testUserData);

      LogService::info("Test user created successfully.\n");
    }
  }
}
