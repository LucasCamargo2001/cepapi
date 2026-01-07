<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class CepControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private function decodeJson(): array
    {
        $this->assertContentType('application/json');

        $body = (string)$this->_response->getBody();
        $json = json_decode($body, true);

        $this->assertIsArray($json);

        return $json;
    }

    public function testInvalidCepReturns400(): void
    {
        $this->get('/api/cep/123');

        $this->assertResponseCode(400);
        $json = $this->decodeJson();

        $this->assertFalse($json['success']);
        $this->assertNull($json['data']);
        $this->assertSame(400, $json['error']['status']);
        $this->assertNotEmpty($json['error']['message']);
    }

    public function testValidCepWithHyphenReturns200(): void
    {
        $this->get('/api/cep/01001-000');

        $this->assertResponseOk();
        $json = $this->decodeJson();

        $this->assertTrue($json['success']);
        $this->assertNull($json['error']);

        $this->assertSame('01001000', $json['data']['cep']);
        $this->assertSame('SP', $json['data']['state']);
        $this->assertSame('São Paulo', $json['data']['city']);
        $this->assertSame('Sé', $json['data']['neighborhood']);
        $this->assertSame('Praça da Sé', $json['data']['street']);
        $this->assertSame('viacep', $json['data']['service']);
    }

    public function testCepNotFoundReturns404(): void
    {
        $this->get('/api/cep/00000000');

        $this->assertResponseCode(404);
        $json = $this->decodeJson();

        $this->assertFalse($json['success']);
        $this->assertNull($json['data']);
        $this->assertSame(404, $json['error']['status']);
        $this->assertNotEmpty($json['error']['message']);
    }

    public function testViaCepContractIsNormalized(): void
    {
        $this->get('/api/cep/01001000');

        $this->assertResponseOk();
        $json = $this->decodeJson();

        $this->assertArrayHasKey('success', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('error', $json);

        $this->assertTrue($json['success']);
        $this->assertNull($json['error']);

        $data = $json['data'];
        $this->assertArrayHasKey('cep', $data);
        $this->assertArrayHasKey('state', $data);
        $this->assertArrayHasKey('city', $data);
        $this->assertArrayHasKey('neighborhood', $data);
        $this->assertArrayHasKey('street', $data);
        $this->assertArrayHasKey('service', $data);

        $this->assertSame('01001000', $data['cep']);
        $this->assertSame('viacep', $data['service']);
    }
}
