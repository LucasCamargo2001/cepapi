<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Exception\CepNotFoundException;
use App\Service\Exception\UpstreamInvalidResponseException;
use App\Service\Exception\UpstreamTimeoutException;
use App\Service\Exception\UpstreamUnavailableException;
use App\Service\Mapper\CepResponseMapper;
use App\Support\ProdLog;
use Cake\Cache\Cache;
use Cake\Http\Client;

class CepService
{
    private const URL = 'https://viacep.com.br/ws/%s/json/';
    private const CACHE_CONFIG = 'cep';
    private const CACHE_KEY_PREFIX = 'cep_';
    private const TIMEOUT_SECONDS = 3;

    public function fetch(string $cep, ?string $requestId = null): array
    {
        $cep = $this->onlyDigits($cep);

        if (strlen($cep) !== 8) {
            ProdLog::warning('cep_invalid_service', [
                'request_id' => $requestId,
                'cep' => $cep,
            ]);

            throw new UpstreamInvalidResponseException('Formato de CEP inválido.');
        }

        $cacheKey = self::CACHE_KEY_PREFIX . $cep;

        $cached = Cache::read($cacheKey, self::CACHE_CONFIG);
        if (is_array($cached)) {
            ProdLog::info('cep_cache_hit', [
                'request_id' => $requestId,
                'cep' => $cep,
            ]);

            $cached['service'] = 'cache';
            return $cached;
        }

        ProdLog::info('cep_cache_miss', [
            'request_id' => $requestId,
            'cep' => $cep,
        ]);

        $http = new Client(['timeout' => self::TIMEOUT_SECONDS]);
        $t0 = microtime(true);

        try {
            $response = $http->get(sprintf(self::URL, $cep));
        } catch (\Throwable) {
            ProdLog::error('viacep_unavailable', [
                'request_id' => $requestId,
                'cep' => $cep,
            ]);

            throw new UpstreamUnavailableException('Falha ao consultar o serviço de CEP.');
        }

        $durationMs = (int)round((microtime(true) - $t0) * 1000);
        $status = $response->getStatusCode();

        ProdLog::info('viacep_response', [
            'request_id' => $requestId,
            'cep' => $cep,
            'status' => $status,
            'duration_ms' => $durationMs,
        ]);

        if ($status === 408) {
            throw new UpstreamTimeoutException('Timeout ao consultar o serviço de CEP.');
        }

        if ($status >= 500) {
            throw new UpstreamUnavailableException('Serviço de CEP indisponível no momento.');
        }

        $json = $response->getJson();
        if (!is_array($json)) {
            throw new UpstreamInvalidResponseException('Resposta inválida do serviço de CEP.');
        }

        if (!empty($json['erro'])) {
            throw new CepNotFoundException('CEP não encontrado.');
        }

        if (empty($json['uf']) || empty($json['localidade'])) {
            throw new UpstreamInvalidResponseException('Resposta incompleta do serviço de CEP.');
        }

        $normalized = CepResponseMapper::map($json);
        $normalized['cep'] = $cep;
        $normalized['service'] = 'viacep';

        Cache::write($cacheKey, $normalized, self::CACHE_CONFIG);

        return $normalized;
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
