<?php
namespace Domain\Address;

class Address {
  private $id;
  private $customerId;
  private $street;
  private $number;
  private $zipcode;
  private $city;
  private $state;

  public function __construct($id, $customerId, $street, $number, $zipcode, $city, $state) {
    $this->id = $id;
    $this->customerId = $customerId;
    $this->street = $street;
    $this->number = $number;
    $this->zipcode = $zipcode;
    $this->city = $city;
    $this->state = $state;

    $this->validate();
  }

  public function getId() { return $this->id; }
  public function getCustomerId() { return $this->customerId; }
  public function getStreet() { return $this->street; }
  public function getNumber() { return $this->number; }
  public function getZipcode() { return $this->zipcode; }
  public function getCity() { return $this->city; }
  public function getState() { return $this->state; }

  private function validate() {
    $errors = [];

    if (empty($this->street)) {
      $errors[] = 'Street is required.';
    } elseif (strlen($this->street) < 3) {
      $errors[] = 'Street must be at least 3 characters.';
    } elseif (strlen($this->street) > 256) {
      $errors[] = 'Street must be no more than 256 characters.';
    }

    if (empty($this->number))
      $errors[] = 'Number is required.';

    if (empty($this->zipcode) || !preg_match('/^[0-9]{5}\-[0-9]{3}$/', $this->zipcode))
      $errors[] = 'Invalid postal code format. Expected format: #####-###';

    if (empty($this->city))
      $errors[] = 'City is required.';
    
    if (empty($this->state))
      $errors[] = 'State is required.';
    
    if (!empty($errors)) 
      throw new \InvalidArgumentException(implode(' ', $errors));
  }
}
