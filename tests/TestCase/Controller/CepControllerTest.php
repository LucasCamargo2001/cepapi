<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class CepControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Garante que cada teste rode "do zero" (sem depender de cache anterior)
        Cache::clear('cep');
    }

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

        $this->assertFalse($json['sucesso']);
        $this->assertNull($json['dados']);
        $this->assertSame(400, $json['erro']['status']);
        $this->assertNotEmpty($json['erro']['mensagem']);
    }

    public function testValidCepWithHyphenReturns200(): void
    {
        $this->get('/api/cep/01001-000');

        $this->assertResponseOk();
        $json = $this->decodeJson();

        $this->assertTrue($json['sucesso']);
        $this->assertNull($json['erro']);

        $dados = $json['dados'];

        $this->assertSame('01001000', $dados['cep']);
        $this->assertSame('SP', $dados['uf']);
        $this->assertSame('São Paulo', $dados['cidade']);
        $this->assertSame('Sé', $dados['bairro']);
        $this->assertSame('Praça da Sé', $dados['logradouro']);
        $this->assertSame('viacep', $dados['service']);
    }

    public function testCepNotFoundReturns404(): void
    {
        $this->get('/api/cep/00000000');

        $this->assertResponseCode(404);
        $json = $this->decodeJson();

        $this->assertFalse($json['sucesso']);
        $this->assertNull($json['dados']);
        $this->assertSame(404, $json['erro']['status']);
        $this->assertNotEmpty($json['erro']['mensagem']);
    }

    public function testViaCepContractIsNormalized(): void
    {
        $this->get('/api/cep/01001000');

        $this->assertResponseOk();
        $json = $this->decodeJson();

        $this->assertArrayHasKey('sucesso', $json);
        $this->assertArrayHasKey('dados', $json);
        $this->assertArrayHasKey('erro', $json);

        $this->assertTrue($json['sucesso']);
        $this->assertNull($json['erro']);

        $dados = $json['dados'];

        $this->assertArrayHasKey('cep', $dados);
        $this->assertArrayHasKey('logradouro', $dados);
        $this->assertArrayHasKey('complemento', $dados);
        $this->assertArrayHasKey('bairro', $dados);
        $this->assertArrayHasKey('cidade', $dados);
        $this->assertArrayHasKey('uf', $dados);
        $this->assertArrayHasKey('service', $dados);

        $this->assertSame('01001000', $dados['cep']);
        $this->assertSame('viacep', $dados['service']);
    }
}
