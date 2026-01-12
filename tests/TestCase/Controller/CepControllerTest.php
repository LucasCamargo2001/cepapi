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

    /**
     * Decodifica o JSON e, se falhar, já imprime status + body para facilitar debug.
     */
    private function decodeJson(): array
    {
        $this->assertContentType(
            'application/json',
            $this->debugResponse('Content-Type inesperado (esperava application/json)')
        );

        $body = (string)$this->_response->getBody();
        $this->assertNotSame(
            '',
            trim($body),
            $this->debugResponse('Body veio vazio')
        );

        $json = json_decode($body, true);

        $this->assertIsArray(
            $json,
            $this->debugResponse('Body não é um JSON válido')
        );

        return $json;
    }

    /**
     * Gera uma string de debug bem útil quando o teste falha.
     */
    private function debugResponse(string $headline = 'Falha'): string
    {
        $status = $this->_response?->getStatusCode();
        $contentType = $this->_response?->getHeaderLine('Content-Type');
        $body = (string)$this->_response?->getBody();

        // Evita log gigante, mas ainda mostra o essencial.
        $bodyPreview = mb_substr($body, 0, 2000);

        return $headline
            . "\nHTTP: {$status}"
            . "\nContent-Type: {$contentType}"
            . "\nBody (até 2000 chars):\n{$bodyPreview}\n";
    }

    /**
     * Helper para validar o "contrato" padrão da API.
     */
    private function assertApiEnvelope(array $json): void
    {
        $this->assertArrayHasKey('sucesso', $json, 'Payload deve ter a chave raiz "sucesso".' . $this->debugResponse());
        $this->assertArrayHasKey('dados', $json, 'Payload deve ter a chave raiz "dados".' . $this->debugResponse());
        $this->assertArrayHasKey('erro', $json, 'Payload deve ter a chave raiz "erro".' . $this->debugResponse());
    }

    /**
     * Helper para validar resposta de erro padronizada.
     */
    private function assertErrorPayload(array $json, int $statusEsperado): void
    {
        $this->assertApiEnvelope($json);

        $this->assertFalse($json['sucesso'], 'Esperava sucesso=false em resposta de erro.' . $this->debugResponse());
        $this->assertNull($json['dados'], 'Em erro, "dados" deve ser null.' . $this->debugResponse());

        $this->assertIsArray($json['erro'], '"erro" deve ser um objeto (array).' . $this->debugResponse());
        $this->assertArrayHasKey('status', $json['erro'], '"erro" deve conter "status".' . $this->debugResponse());
        $this->assertArrayHasKey('mensagem', $json['erro'], '"erro" deve conter "mensagem".' . $this->debugResponse());

        $this->assertSame(
            $statusEsperado,
            $json['erro']['status'],
            "Status do payload deve ser {$statusEsperado}." . $this->debugResponse()
        );
        $this->assertNotEmpty(
            $json['erro']['mensagem'],
            'Mensagem de erro deve vir preenchida.' . $this->debugResponse()
        );
    }

    /**
     * Helper para validar o "shape" de dados normalizados.
     */
    private function assertNormalizedCepData(array $dados): void
    {
        foreach (['cep', 'logradouro', 'complemento', 'bairro', 'cidade', 'uf', 'service'] as $key) {
            $this->assertArrayHasKey($key, $dados, "Dados normalizados devem conter '{$key}'." . $this->debugResponse());
        }
    }

    public static function invalidCepProvider(): array
    {
        return [
            'muito curto' => ['123'],
            'com letras' => ['12A45-000'],          // vira 1245000 (7 dígitos)
            'sete digitos com pontuacao' => ['01.001-00'], // vira 0100100 (7 dígitos)
            'nove digitos' => ['01001-0000'],       // vira 010010000 (9 dígitos)
        ];
    }


    /**
     * @dataProvider invalidCepProvider
     */
    public function testInvalidCepReturns400(string $cep): void
    {
        $this->get("/api/cep/{$cep}");

        $this->assertResponseCode(400, $this->debugResponse("Esperava HTTP 400 para cep='{$cep}'"));

        $json = $this->decodeJson();
        $this->assertErrorPayload($json, 400);
    }

    public function testValidCepWithHyphenReturns200(): void
    {
        $this->get('/api/cep/01001-000');

        $this->assertResponseOk($this->debugResponse('Esperava HTTP 200 para CEP válido com hífen'));

        $json = $this->decodeJson();
        $this->assertApiEnvelope($json);

        $this->assertTrue($json['sucesso'], 'Esperava sucesso=true.' . $this->debugResponse());
        $this->assertNull($json['erro'], 'Em sucesso, "erro" deve ser null.' . $this->debugResponse());

        $dados = $json['dados'];
        $this->assertIsArray($dados, '"dados" deve ser um objeto (array).' . $this->debugResponse());
        $this->assertNormalizedCepData($dados);

        $this->assertSame('01001000', $dados['cep'], 'CEP deve ser normalizado (apenas dígitos).' . $this->debugResponse());
        $this->assertSame('SP', $dados['uf'], 'UF incorreta.' . $this->debugResponse());
        $this->assertSame('São Paulo', $dados['cidade'], 'Cidade incorreta.' . $this->debugResponse());
        $this->assertSame('Sé', $dados['bairro'], 'Bairro incorreto.' . $this->debugResponse());
        $this->assertSame('Praça da Sé', $dados['logradouro'], 'Logradouro incorreto.' . $this->debugResponse());
        $this->assertSame('viacep', $dados['service'], 'Service deve indicar a origem (viacep).' . $this->debugResponse());
    }

    public function testCepNotFoundReturns404(): void
    {
        $this->get('/api/cep/00000000');

        $this->assertResponseCode(404, $this->debugResponse('Esperava HTTP 404 quando CEP não existe'));

        $json = $this->decodeJson();
        $this->assertErrorPayload($json, 404);
    }

    public function testViaCepContractIsNormalized(): void
    {
        $this->get('/api/cep/01001000');

        $this->assertResponseOk($this->debugResponse('Esperava HTTP 200 para CEP válido (contrato normalizado)'));

        $json = $this->decodeJson();
        $this->assertApiEnvelope($json);

        $this->assertTrue($json['sucesso'], 'Esperava sucesso=true.' . $this->debugResponse());
        $this->assertNull($json['erro'], 'Em sucesso, "erro" deve ser null.' . $this->debugResponse());

        $dados = $json['dados'];
        $this->assertIsArray($dados, '"dados" deve ser um objeto (array).' . $this->debugResponse());
        $this->assertNormalizedCepData($dados);

        $this->assertSame('01001000', $dados['cep'], 'CEP normalizado incorreto.' . $this->debugResponse());
        $this->assertSame('viacep', $dados['service'], 'Service incorreto.' . $this->debugResponse());
    }
}
