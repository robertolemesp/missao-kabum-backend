<?php
namespace Application\Tests\E2E;

use PHPUnit\Framework\TestCase;

use Infrastructure\DependencyInjection\DependencyInjectionContainer;

use Infrastructure\Database\Setup\DatabaseSetup;
use Infrastructure\Database\Connection\DatabaseConnection;
use Infrastructure\Database\Seeder\TestUserSeeder;

use Infrastructure\Api\Auth\Seeder\AuthUserSeeder;

use Exception;

class ApiE2eTest extends TestCase {
  private $apiBaseUrl;
  private $authToken;

  protected function setUp(): void {
    parent::setUp();

    $APP_DEFAULT_PORT = '8000';

    $env = $_ENV['APP_ENV'] ?? 'development';

    if ($env === 'production') {
      $protocol = 'https';
      $port = '443';
    } else {
      $protocol = 'http';
      $port = $_ENV['PORT'] ?? $APP_DEFAULT_PORT;
    }

    $this->apiBaseUrl = "$protocol://localhost:$port";

    DatabaseSetup::clearTestDatabase();
    $pdo = DatabaseConnection::getConnection();

    $container = new DependencyInjectionContainer($pdo);

    $testDbUserSeeder = new TestUserSeeder($container->getCustomerService());
    $testDbUserSeeder->seed();

    $testAuthUserSeeder = new AuthUserSeeder();
    $testAuthUserSeeder->seed();

    $tokenPath = __DIR__ . '/../Auth/test_jwt_token.txt';
    if (!file_exists($tokenPath)) 
      throw new Exception('Test token file not found. Ensure the test seeder ran.');
    
    $this->authToken = trim(file_get_contents($tokenPath));
  }

  public static function tearDownAfterClass(): void {
    //DatabaseSetup::clearTestDatabase();
  }

  private function isRunningInDocker(): bool {
    return file_exists('/.dockerenv') || getenv('DOCKER_ENV') === 'true';
  }

  private function sendRequest($method, $url, $data = null, $auth = true) {
    $headers = ['Content-Type: application/json'];

    if ($auth && $this->authToken) 
      $headers[] = 'Authorization: Bearer ' . $this->authToken;

    $isProduction = $_ENV['APP_ENV'] === 'production';

    $options = [
      'http' => [
        'method'  => $method,
        'header'  => implode('\r\n', $headers),
        'content' => $data ? json_encode($data) : '',
        'ignore_errors' => true
      ],
      'ssl' => [
        'verify_peer' => $isProduction,
        'verify_peer_name' => $isProduction,
        'cafile' => $isProduction ? '/etc/ssl/certs/ca-certificates.crt' : null
      ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($this->apiBaseUrl . $url, false, $context);

    if ($response === false) 
      throw new Exception('Request to $url failed. Ensure the server is running.');

    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE)
      throw new Exception('Invalid JSON response from API: ' . var_export($response, true));

    return $decodedResponse;
  }
  
  public function testCreateCustomer() {
    $data = [
      'name' => 'Roberto Lemes',
      'email' => 'roberto@missao-kabum.com',
      'password' => 'AlmostSecurePass123!',
      'birthday' => '1995-01-11',
      'cpf' => '123.456.789-11',
      'rg' => '13.345.678-1',
      'phone' => '(11) 94815-3588',
      'addresses' => [
        [
          'street' => 'Rua Durval Clemente',
          'number' => '1',
          'zipcode' => '12345-678',
          'city' => 'São Paulo',
          'state' => 'SP'
        ]
      ]
    ];

    $response = $this->sendRequest('POST', '/customer', $data);

    $this->assertArrayHasKey("message", $response, "Failed to create customer.");
  }

  // public function testValidateCustomerCredentials() {
  //   $data = [
  //     'email' => 'roberto@missao-kabum.com', 
  //     'password' => 'AlmostSecurePass123!'
  //   ];
  
  //   $response = $this->sendRequest('POST', '/customer/credentials', $data, false);

  //   $this->assertNotNull($response, 'Response should not be null.');
  //   $this->assertIsBool($response, 'Response should be a boolean.');
  //   $this->assertTrue($response, 'Credentials should be valid.');
  // }
  
  public function testGetCustomers() {
    $response = $this->sendRequest('GET', '/customer');

    $this->assertIsArray($response, 'Expected an array of customers.');
  }

  public function testUpdateCustomer() {
    $data = [
      'name' => 'Roberto Lemes Padilha',
      'email' => 'test_user@seeding.com', // instead timeouting, or process/db transaction handling, we will test on the seeded user
      'birthday' => '1995-11-01',
      'cpf' => '440.348.488-83',
      'rg' => '43.326.135-3',
      'phone' => '(11) 94815-3583'
    ];
    
    $response = $this->sendRequest('PUT', '/customer/1', $data);

    $this->assertArrayHasKey("message", $response, "Failed to update customer.");
  }

  public function testCreateCustomerAddress() {
    $data = [
      [
        'street' => 'Rua Nova',
        'number' => '1',
        'zipcode' => '54321-876',
        'city' => 'São Paulo',
        'state' => 'SP'
      ],
      [
        'street' => 'Avenida Nova',
        'number' => '2',
        'zipcode' => '65432-987',
        'city' => 'Florianópolis',
        'state' => 'SC'
      ]
    ];

    $response = $this->sendRequest('POST', '/customer/1/address', $data);
    $this->assertArrayHasKey("message", $response, "Failed to create addresses.");
  }

  public function testUpdateCustomerAddress() {
    $data = [
      [
        'id' => 1,
        'street' => 'Rua Nova Atualizada',
        'number' => '300',
        'zipcode' => '54321-876',
        'city' => 'Teresina',
        'state' => 'PI'
      ]
    ];

    $response = $this->sendRequest('PUT', '/customer/1/address', $data);
    $this->assertArrayHasKey("message", $response, "Failed to update address.");
  }

  public function testRemoveCustomerAddress() {
    $data = ['addressIds' => [1, 2]];

    $response = $this->sendRequest('DELETE', '/customer/1/address', $data);
    $this->assertArrayHasKey("message", $response, "Failed to remove addresses.");
  }

  public function testDeleteCustomer() {
    $response = $this->sendRequest('DELETE', '/customer/1');
    $this->assertArrayHasKey("message", $response, "Failed to delete customer.");
  }
}
