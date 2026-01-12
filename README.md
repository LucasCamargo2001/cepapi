# CEP API – CakePHP

API REST simples para consulta de CEP, utilizando uma API pública, normalizando o retorno e expondo um contrato JSON padronizado.

---

## Tecnologias utilizadas
- PHP 8.2+
- CakePHP 5
- Composer
- PHPUnit
- Docker
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
GET /api/cep/03314-000

**Response – 200**
```json
{
  "sucesso": true,
  "dados": {
    "cep": "03314000",
    "logradouro": "Rua Vilela",
    "complemento": "de 391/392 ao fim",
    "bairro": "Tatuapé",
    "cidade": "São Paulo",
    "uf": "SP",
    "service": "viacep"
  },
  "erro": null
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
 │   ├── Exception/
 │   │   └── CepExceptions.php
 │   └── Mapper/
 │       └── CepResponseMapper.php
 │
 └── Middleware/
     └── RequestIdMiddleware.php

tests/
 └── TestCase/
     └── Controller/
         └── CepControllerTest.php
```

---

## Logs e observabilidade

A aplicação utiliza **logs estruturados em JSON**, escritos diretamente no **stdout**.

Essa abordagem facilita:
- Visualização em tempo real no terminal
- Uso em containers Docker
- Integração futura com ferramentas como Grafana, Loki ou Elastic Stack

### Exemplo de logs

```text
2026-01-11 23:39:32 info: {"event":"cep_cache_hit","ts":"2026-01-11T23:39:32+00:00","request_id":"3ddfb6ab66f0aa151fa8f6b7cac17fa6","cep":"02243030"}

2026-01-11 23:39:32 info: {"event":"request_end","ts":"2026-01-11T23:39:32+00:00","request_id":"3ddfb6ab66f0aa151fa8f6b7cac17fa6","route":"GET /api/cep/{cep}","cep":"02243030","status":200,"duration_ms":3}
```

### Características dos logs
- `request_id` para correlação entre eventos da mesma requisição
- `event` identifica o tipo do evento (ex: `request_end`, `cep_cache_hit`)
- `duration_ms` mede o tempo de processamento
- Nenhum dado sensível é logado

> Atualmente os logs não são persistidos em arquivo, sendo exibidos no mesmo terminal onde o servidor está em execução.

---

## Testes automatizados

Os testes automatizados cobrem:
- CEP inválido
- CEP com hífen
- CEP não encontrado
- Contrato JSON padronizado
- Integração real com a API ViaCEP

Para garantir previsibilidade, o cache é limpo automaticamente antes de cada teste.

### Executar os testes

```bash
composer install
vendor/bin/phpunit
```

> Os testes assumem acesso à internet para consulta ao ViaCEP.

---

## Executar o projeto localmente

### Windows
```bash
composer install
php bin\cake.php server
```

### Linux / macOS
```bash
composer install
bin/cake server
```

Acessar:  
http://localhost:8765/api/cep/01001000

---

## Executar com Docker (imagem pronta)

```bash
docker pull lucascamargo2001/cep-api:latest
docker run --rm -p 8765:8765 lucascamargo2001/cep-api:latest
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
- Logs estruturados com `request_id` para rastreabilidade.
- Uso de SQLite em memória apenas para satisfazer o bootstrap do CakePHP.

---

## Melhorias futuras

- Diferenciação do tipo de erro nos logs (ex: `invalid_cep`, `not_found`, `upstream_timeout`).
- Persistência de logs em arquivo ou integração com Loki/Grafana.
- Cache negativo para CEP inexistente.
- Retry automático para falhas temporárias.
- Testes com mock da API externa.

---

## Autor

Lucas Camargo

Projeto desenvolvido como desafio técnico para avaliação de Programador Júnior.

