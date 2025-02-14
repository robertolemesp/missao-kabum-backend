<?php
namespace Infrastructure\Api\Router\Customer;

use Infrastructure\Api\Controller\CustomerController;

class Routes {
  public static function register(array &$router, CustomerController $controller) {
    $router['POST']['/customer'] = function () use ($controller) {
      $controller->create();
    };

    $router['PUT']['/customer/(\d+)'] = function ($id) use ($controller) {
      $controller->update((int) $id);
    };

    $router['GET']['/customer'] = function () use ($controller) {
      $controller->list();
    };

    $router['DELETE']['/customer/(\d+)'] = function ($id) use ($controller) {
      $controller->remove((int) $id);
    };

    $router['POST']['/customer/(\d+)/address'] = function ($customerId) use ($controller) {
      $controller->updateAddress((int) $customerId);
    };

    $router['PUT']['/customer/(\d+)/address'] = function ($customerId) use ($controller) {
      $controller->updateAddress((int) $customerId);
    };

    $router['DELETE']['/customer/(\d+)/address'] = function ($customerId) use ($controller) {
      $controller->removeAddress((int) $customerId);
    };
  }
}
