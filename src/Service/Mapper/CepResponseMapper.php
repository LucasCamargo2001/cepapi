<?php
declare(strict_types=1);

namespace App\Service\Mapper;

class CepResponseMapper
{  public static function map(array $data): array
    {
         return [
            'cep' => preg_replace('/\D+/', '', $data['cep'] ?? ''),
            'logradouro' => $data['logradouro'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'localidade' => $data['localidade'] ?? null,
            'uf' => $data['uf'] ?? null,
        ];
    }
}
