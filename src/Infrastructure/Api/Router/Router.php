<?php
namespace Infrastructure\Api\Router;

use Infrastructure\Api\Router\Customer\Routes as CustomerRoutes;
use Application\Customer\CustomerService;
use Application\Address\AddressService;

use Infrastructure\Api\Controller\CustomerController;

class Router {
  private $customerController;

  public function __construct(CustomerService $customerService, AddressService $addressService) {
    $this->customerController = new CustomerController($customerService, $addressService);
  }

  public function route($requestUri, $requestMethod) {
    $router = [
      'POST' => [],
      'GET' => [],
      'PUT' => [],
      'DELETE' => []
    ];

    $router['GET']['/'] = function () {
      header('Content-Type: application/json');
      echo json_encode(['message' => 'Application is Running']);
    };
    
    CustomerRoutes::register($router, $this->customerController);

    if (!isset($router[$requestMethod])) {
      header('HTTP/1.1 405 Method Not Allowed');
      echo json_encode(['message' => 'Method Not Allowed']);
      
      return;
    }

    foreach ($router[$requestMethod] as $pattern => $handler) {
      if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
        array_shift($matches);
        call_user_func_array($handler, $matches);

        return;
      }
    }

    header('HTTP/1.1 404 Not Found');
    echo json_encode(['message' => 'Route Not Found']);
  }
}
