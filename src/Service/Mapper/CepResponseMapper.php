<?php
declare(strict_types=1);

namespace App\Service\Mapper;

class CepResponseMapper
{  public static function map(array $data): array
    {
         return [
            'CEP' => preg_replace('/\D+/', '', $data['cep'] ?? ''),
            'Logradouro' => $data['logradouro'] ?? null,
            'Complemento' => $data['complemento'] ?? null,
            'Bairro' => $data['bairro'] ?? null,
            'Cidade' => $data['localidade'] ?? null,
            'UF' => $data['uf'] ?? null,
        ];
    }
}
