<?php
namespace Infrastructure\Api\Controller;

use ReflectionClass;

use Application\Customer\CustomerService;
use Application\Address\AddressService;

use Infrastructure\Logging;

class CustomerController {
  private $customerService;
  private $addressService;

  public function __construct(CustomerService $customerService, AddressService $addressService) {
    $this->customerService = $customerService;
    $this->addressService = $addressService;
  }

  private function jsonResponse(array $data, int $statusCode): void {
    header('Content-Type: application/json');
    http_response_code($statusCode);

    $data = array_map(fn($item) => is_object($item) ? $this->objectToArray($item) : $item, $data);

    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    exit; 
  }

  private function objectToArray(object $obj): array {
    $reflectionClass = new ReflectionClass($obj);
    $properties = $reflectionClass->getProperties();
    $array = [];

    foreach ($properties as $property) {
      $property->setAccessible(true);
      $value = $property->getValue($obj);

      if ($value instanceof DateTime) 
        $value = $value->format('Y-m-d');

      $array[$property->getName()] = $value;
    }

    return $array;
  }



  public function create(): void {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
      $this->customerService->create($data);
      $this->jsonResponse(['message' => 'Customer created successfully.'], 201);
    } catch (\InvalidArgumentException $e) {
      $this->jsonResponse(['errors' => explode(' ', $e->getMessage())], 400);
    } catch (\Exception $e) {
      $this->jsonResponse(['message' => $e->getMessage()], 500);
    }
  }

  public function update(int $id): void {
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);
  
    if (json_last_error() !== JSON_ERROR_NONE) 
      $this->jsonResponse(['message' => 'Invalid JSON input.', 'error' => json_last_error_msg()], 400);
  
    try {
      if (!isset($data['email'])) 
        $this->jsonResponse(['message' => 'Missing required field: email.'], 400);
  
      try {
        if (!$this->customerService->exists($data['email'])) 
          $this->jsonResponse(['message' => 'Customer not found.'], 404);
      } catch (\Exception $e) {
        $this->jsonResponse(['message' => 'Error checking customer existence: ' . $e->getMessage()], 500);
      }
  
      $data['id'] = $id;
      $this->customerService->update($data);

      $this->jsonResponse(['message' => 'Customer updated successfully.'], 200);
    } catch (\InvalidArgumentException $e) {
      $this->jsonResponse(['errors' => explode(' ', $e->getMessage())], 400);
    } catch (\Exception $e) {
      LogService::error("Update Customer Error: " . $e->getMessage());
      $this->jsonResponse(['message' => 'Internal Server Error.'], 500);
    }
  }
  

  public function remove(int $id): void {
    try {
      $this->customerService->remove($id);

      $this->jsonResponse(['message' => 'Customer deleted successfully.'], 200);
    } catch (\Exception $e) {
      $this->jsonResponse(['message' => $e->getMessage()], 500);
    }
  }

  public function list(): void {
    $customers = $this->customerService->list();
    $this->jsonResponse($customers, 200);
  }

  public function updateAddress(int $customerId): void {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
      if (empty($data['addresses'])) {
        $this->addressService->removeAllByCustomerId($customerId);
        $this->jsonResponse(['message' => 'All addresses removed successfully.'], 200);
      }

      $this->addressService->updateMany($data['addresses']);
      $this->jsonResponse(['message' => 'Addresses updated successfully.'], 200);
    } catch (\Exception $e) {
      $this->jsonResponse(['message' => $e->getMessage()], 500);
    }
  }

  public function removeAddress(int $customerId): void {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
      if (empty($data['addressIds']) || !is_array($data['addressIds']))
        $this->jsonResponse(['message' => 'Invalid or missing addressIds.'], 400);

      $this->addressService->removeMany($data['addressIds']);
      $this->jsonResponse(['message' => 'Addresses removed successfully.'], 200);
    } catch (\Exception $e) {
      $this->jsonResponse(['message' => $e->getMessage()], 500);
    }
  }
}
