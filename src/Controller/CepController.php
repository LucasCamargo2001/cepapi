<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CepService;
use App\Service\Exception\CepNotFoundException;
use App\Service\Exception\UpstreamInvalidResponseException;
use App\Service\Exception\UpstreamTimeoutException;
use App\Service\Exception\UpstreamUnavailableException;
use App\Support\ProdLog;

class CepController extends AppController
{
    public function view(string $cep)
    {
        $t0 = microtime(true);

        $requestId = (string)$this->request->getAttribute('request_id');
        $ip = (string)$this->request->getEnv('REMOTE_ADDR');
        $userAgent = (string)$this->request->getHeaderLine('User-Agent');

        $cepNormalizado = preg_replace('/\D+/', '', $cep) ?? '';

        if (!preg_match('/^\d{8}$/', $cepNormalizado)) {
            ProdLog::warning('cep_invalid', [
                'request_id' => $requestId,
                'cep_in' => $cep,
                'cep' => $cepNormalizado,
                'ip' => $ip,
                'ua' => $userAgent,
            ]);

            $response = $this->jsonError(
                400,
                'Formato de CEP inválido. Use 8 dígitos (ex: 01001000 ou 01001-000).'
            );

            $this->logRequestEnd($t0, 400, $requestId, $cepNormalizado);

            return $response;
        }

        $service = new CepService();

        try {
            $dados = $service->fetch($cepNormalizado, $requestId);
        } catch (CepNotFoundException $e) {
            ProdLog::notice('cep_not_found', [
                'request_id' => $requestId,
                'cep' => $cepNormalizado,
            ]);

            $response = $this->jsonError(404, $e->getMessage());
            $this->logRequestEnd($t0, 404, $requestId, $cepNormalizado);
            return $response;
        } catch (UpstreamTimeoutException $e) {
            ProdLog::error('viacep_timeout', [
                'request_id' => $requestId,
                'cep' => $cepNormalizado,
            ]);

            $response = $this->jsonError(504, $e->getMessage());
            $this->logRequestEnd($t0, 504, $requestId, $cepNormalizado);
            return $response;
        } catch (UpstreamUnavailableException $e) {
            ProdLog::error('viacep_unavailable', [
                'request_id' => $requestId,
                'cep' => $cepNormalizado,
            ]);

            $response = $this->jsonError(503, $e->getMessage());
            $this->logRequestEnd($t0, 503, $requestId, $cepNormalizado);
            return $response;
        } catch (UpstreamInvalidResponseException $e) {
            ProdLog::error('viacep_invalid_response', [
                'request_id' => $requestId,
                'cep' => $cepNormalizado,
            ]);

            $response = $this->jsonError(502, $e->getMessage());
            $this->logRequestEnd($t0, 502, $requestId, $cepNormalizado);
            return $response;
        } catch (\Throwable $e) {
            ProdLog::error('unexpected_error', [
                'request_id' => $requestId,
                'cep' => $cepNormalizado,
                'exception' => get_class($e),
            ]);

            $response = $this->jsonError(500, 'Erro inesperado.');
            $this->logRequestEnd($t0, 500, $requestId, $cepNormalizado);
            return $response;
        }

        $response = $this->jsonSuccess($dados);
        $this->logRequestEnd($t0, 200, $requestId, $cepNormalizado);

        return $response;
    }

    private function logRequestEnd(float $t0, int $status, string $requestId, string $cep): void
    {
        $ms = (int)round((microtime(true) - $t0) * 1000);

        ProdLog::info('request_end', [
            'request_id' => $requestId,
            'route' => 'GET /api/cep/{cep}',
            'cep' => $cep,
            'status' => $status,
            'duration_ms' => $ms,
        ]);
    }

    private function jsonSuccess(array $dados)
    {
        $payload = [
            'sucesso' => true,
            'dados' => $dados,
            'erro' => null,
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
    }

    private function jsonError(int $status, string $mensagem)
    {
        $payload = [
            'sucesso' => false,
            'dados' => null,
            'erro' => [
                'mensagem' => $mensagem,
                'status' => $status,
            ],
        ];

        return $this->response
            ->withStatus($status)
            ->withType('application/json')
            ->withStringBody(
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
    }
}
