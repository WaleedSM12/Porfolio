# Travel Deals Aggregator

A Laravel application that fetches and aggregates travel deals from third-party APIs and exposes a RESTful API for users to browse, search, and bookmark deals.

## Features

- Authentication with Laravel Sanctum
- Integration with travel deals APIs (Skyscanner, Amadeus)
- Bookmarking system for users to save favorite deals
- Rate limiting to protect API endpoints
- Queue system for processing heavy tasks
- Scheduled tasks to fetch new deals


## Installation

1. Clone the repository:
```bash
git clone https://github.com/WaleedSM12/Porfolio.git
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment variables:
```bash
cp .env.example .env
```

4. Update the `.env` file with your database credentials and API keys:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=travel_deals
DB_USERNAME=root
DB_PASSWORD=

AMADEUS_API_KEY=your_amadeus_api_key
AMADEUS_API_SECRET=your_amadeus_api_secret
```

5. Run migrations and seed the database:
```bash
php artisan migrate --seed
```
