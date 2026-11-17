# RentACar – Laravel

Marketplace de aluguel de carros: donos anunciam veículos, motoristas buscam por cidade/período e solicitam o aluguel; o dono aprova ou recusa.

## Instalação (Laravel 11/12, PHP 8.2+)

```bash
composer create-project laravel/laravel rentacar && cd rentacar

# Autenticação (login, cadastro, perfil)
composer require laravel/breeze --dev
php artisan breeze:install blade

# Copie as pastas deste pacote (app/, database/, resources/, routes/) por cima do projeto
# (sobrescreva routes/web.php)

# Configure o banco no .env e rode:
php artisan migrate
php artisan storage:link   # fotos dos veículos

npm install && npm run dev
php artisan serve
```

## Como funciona

| Quem | Ação | Rota |
|---|---|---|
| Visitante | Buscar carros (cidade, período, preço máx.) | `/` |
| Visitante | Ver detalhes e períodos já reservados | `/veiculos/{id}` |
| Dono | Cadastrar/editar/pausar/excluir veículos | `/meus-veiculos` |
| Dono | Aprovar/recusar solicitações | `/solicitacoes` |
| Motorista | Solicitar aluguel e cancelar reservas | `/minhas-reservas` |

Um mesmo usuário pode ser dono e motorista (mas não pode alugar o próprio carro).

## Regras de negócio

- Período inclusivo: retirada e devolução contam como diárias (`total = dias × diária`).
- Só reservas **confirmadas** bloqueiam a agenda; pendentes podem coexistir.
- Ao aprovar uma reserva, pendentes conflitantes são recusadas automaticamente.
- Disponibilidade é revalidada em transação com `lockForUpdate` (evita reservas duplicadas).
- Veículo com reservas ativas não pode ser excluído (apenas pausado).

## Banco de dados

- `vehicles`: user_id (dono), brand, model, year, plate (única), color, transmission, seats, city, daily_price, description, is_active
- `vehicle_photos`: vehicle_id, path, position (0 = capa) — até 8 fotos por veículo
- `rentals`: vehicle_id, user_id (motorista), start_date, end_date, total_price, status (`pending|confirmed|rejected|cancelled`)

## Próximos passos

Pagamento, avaliações, caução, redimensionamento/miniaturas das fotos, e-mails de notificação, painel admin e testes Feature.
