<?php
namespace Infrastructure\DependencyInjection;

use PDO;
use Application\Customer\CustomerService;
use Infrastructure\Repository\Customer\MySQLCustomerRepository;

use Application\Address\AddressService;
use Infrastructure\Repository\Address\MySQLAddressRepository;

use Infrastructure\Api\Router\Router;

class DependencyInjectionContainer {
  private $pdo;
  private $addressService;

  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
    $this->addressService = new AddressService(new MySQLAddressRepository($this->pdo));
  }

  public function getCustomerService(): CustomerService {
    return new CustomerService(new MySQLCustomerRepository($this->pdo), $this->addressService);
  }

  public function getAddressService(): AddressService {
    return $this->addressService;
  }

  public function getRouter(): Router {
    return new Router($this->getCustomerService(), $this->addressService);
  }
}
