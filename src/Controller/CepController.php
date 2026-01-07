<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CepService;
use App\Service\Exception\CepNotFoundException;
use App\Service\Exception\UpstreamInvalidResponseException;
use App\Service\Exception\UpstreamTimeoutException;
use App\Service\Exception\UpstreamUnavailableException;

class CepController extends AppController
{
    public function view(string $cep)
    {
        $normalizedCep = preg_replace('/\D+/', '', $cep) ?? '';

        if (!preg_match('/^\d{8}$/', $normalizedCep)) {
            return $this->jsonError(400, 'Formato de CEP inválido. Use 8 dígitos (ex: 01001000 ou 01001-000).');
        }

        $service = new CepService();

        try {
            $data = $service->fetch($normalizedCep);
        } catch (CepNotFoundException $e) {
            return $this->jsonError(404, $e->getMessage());
        } catch (UpstreamTimeoutException $e) {
            return $this->jsonError(504, $e->getMessage());
        } catch (UpstreamUnavailableException $e) {
            return $this->jsonError(503, $e->getMessage());
        } catch (UpstreamInvalidResponseException $e) {
            return $this->jsonError(502, $e->getMessage());
        } catch (\Throwable) {
            return $this->jsonError(500, 'Erro inesperado.');
        }

        return $this->jsonSuccess($data);
    }

    private function jsonSuccess(array $data)
    {
        $payload = [
            'success' => true,
            'data' => $data,
            'error' => null,
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function jsonError(int $status, string $message)
    {
        $payload = [
            'success' => false,
            'data' => null,
            'error' => [
                'message' => $message,
                'status' => $status,
            ],
        ];

        return $this->response
            ->withStatus($status)
            ->withType('application/json')
            ->withStringBody(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
