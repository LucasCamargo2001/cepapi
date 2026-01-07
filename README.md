# CEP API – CakePHP

API REST simples para consulta de CEP, utilizando uma API pública, normalizando o retorno e expondo um contrato JSON padronizado.

Este projeto foi desenvolvido como parte de um desafio técnico para a vaga de **Programador Júnior**, com foco em clareza, organização do código e tratamento de cenários comuns do dia a dia.

---

## Tecnologias utilizadas
- PHP 8.1+
- CakePHP 5
- Composer
- PHPUnit
- ViaCEP (API pública)

---

## Endpoint

### GET /api/cep/{cep}

Consulta um CEP informado e retorna os dados normalizados.

O CEP pode ser informado com ou sem formatação:
- 01001000
- 01001-000

---

## Exemplo de sucesso

**Request**
GET /api/cep/01001-000

**Response – 200**
```json
{
  "success": true,
  "data": {
    "cep": "01001000",
    "state": "SP",
    "city": "São Paulo",
    "neighborhood": "Sé",
    "street": "Praça da Sé",
    "service": "viacep"
  },
  "error": null
}
```

---

## Tratamento de erros

| Situação                     | Status |
|------------------------------|--------|
| CEP em formato inválido      | 400    |
| CEP não encontrado           | 404    |
| Resposta inválida da API     | 502    |
| Serviço externo indisponível | 503    |
| Timeout na API externa       | 504    |

---

## Estrutura do projeto

src/
 ├── Controller/
 │   └── CepController.php
 │
 ├── Service/
 │   ├── CepService.php
 │   └── Exception/
 │       └── CepExceptions.php
 │
tests/
 └── TestCase/
     └── Controller/
         └── CepControllerTest.php

---

## Testes automatizados

Os testes automatizados cobrem:
- CEP inválido
- CEP com hífen
- CEP não encontrado
- Contrato JSON padronizado
- Integração real com a API ViaCEP

### Executar os testes
composer install
vendor/bin/phpunit

---

## Executar o projeto

composer install
bin/cake server

Acessar:
http://localhost:8765/api/cep/01001000

---

## Decisões técnicas

- Separação da regra de negócio em Service.
- Normalização do CEP antes da validação.
- Respostas JSON padronizadas.
- Tratamento explícito de falhas do serviço externo.
- Uso de testes automatizados para validar o comportamento do endpoint.

---

## Melhorias futuras

- Cache de consultas de CEP.
- Retry automático para falhas temporárias.
- Testes com mock da API externa.
- Logs estruturados.

---

## Autor

Projeto desenvolvido como desafio técnico para avaliação de Programador Júnior.
