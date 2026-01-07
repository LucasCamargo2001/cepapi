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

Observação: resultados de CEPs válidos podem ser retornados a partir de cache para reduzir chamadas ao serviço externo.

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

```
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
```

---

## Testes automatizados

Os testes automatizados cobrem:
- CEP inválido
- CEP com hífen
- CEP não encontrado
- Contrato JSON padronizado
- Integração real com a API ViaCEP

### Executar os testes
```bash
composer install
vendor/bin/phpunit
```

> Os testes assumem acesso à internet para consulta ao ViaCEP.

---

## Executar o projeto localmente

```bash
composer install
bin/cake server
```

Acessar:
http://localhost:8765/api/cep/01001000

---

## Executar com Docker

```bash
docker compose up --build
```

Acessar:
http://localhost:8765/api/cep/01001000

---

## Decisões técnicas

- Separação da regra de negócio em Service.
- Normalização do CEP antes da validação.
- Respostas JSON padronizadas.
- Tratamento explícito de falhas do serviço externo.
- Cache de CEPs válidos para reduzir chamadas ao ViaCEP (TTL configurável).
- Uso de testes automatizados para validar o comportamento do endpoint.

---

## Melhorias futuras

- Cache negativo para CEP inexistente (evitar chamadas repetidas para o mesmo CEP inválido).
- Retry automático para falhas temporárias (com backoff e limite de tentativas).
- Testes com mock da API externa para cenários sem internet/instabilidade do ViaCEP.
- Logs estruturados (correlation id/trace id) para facilitar troubleshooting.

---

## Autor

Lucas Camargo

Projeto desenvolvido como desafio técnico para avaliação de Programador Júnior.
