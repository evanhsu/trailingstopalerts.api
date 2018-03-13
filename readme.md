# Trailing Stop Alerts API
This repo contains the API for creating and triggering trailing stop alerts.
Built on Laravel 5.6.

## Building the project
Clone the project:

    $ git clone https://github.com/evanhsu/trailingstopalerts.api.git
    $ cd trailingstopalerts

Install dependencies with Composer

    $ composer install
    
Add your AlphaVantage API key to `.env` (for retrieving stock prices)

Run database migrations

    $ php artisan db:migrate

Optionally, seed the database with test data

    $php artisan db:seed
    
For a dev environment, you can now make requests to the API using the `client_secret`:
`M3mI547k5W6eeIVXEPhnDsTPfNT8rdXC05UOpyVE` from your frontend.

For a production environment, you need to generate a new OAuth client:

    $ php artisan passport:client --password

That command will generate a `client_id` and `client_secret` that will need to
accompany an authentication request sent from your frontend in order to be
issued a Bearer Token.

    
## Making Requests to the API

In general, each request must be have the following headers:

    Authorization: Bearer jf08234hfpq8w34hf...
    Accept: Application/json
    Content-Type: Application/json
    
The `Content-Type` header is only necessary on requests that have a BODY (post, patch).

The Bearer token is obtained by authenticating with a username/password via the `oauth/token` endpoint.
This is an example using jQuery:

    var settings = {
      "async": true,
      "crossDomain": true,
      "url": "http://localhost/oauth/token",
      "method": "POST",
      "headers": {
        "Content-Type": "application/x-www-form-urlencoded",
        "Cache-Control": "no-cache",
      },
      "data": {
        "grant_type": "password",
        "client_id": "2",
        "client_secret": "M3mI547k5W6eeIVXEPhnDsTPfNT8rdXC05UOpyVE",
        "username": "myemail@example.com",
        "password": "mypassword",
        "scope": "*"
      }
    }

    $.ajax(settings).done(function (response) {
      console.log(response);
    });

The `client_id` and `client_secret` in this example are pre-seeded into the database
for development only.  You'll need to generate a new password grant client for use in
production.
