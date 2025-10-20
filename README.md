# InnovShop

InnovShop is an e-commerce platform built with Symfony, designed for selling innovative tech products.

## Features

-   Product catalogue with categories and filters
-   Product detail pages with reviews
-   Shopping cart (guest & user)
-   Checkout and order management
-   Promotions and new products
-   Admin dashboard (EasyAdmin)
-   User authentication and profile
-   Email notifications for orders

## Installation

1. Clone the repository:

    ```
    git clone https://github.com/Freyjalith/InnovShop.git
    cd innovshop
    ```

2. Install dependencies:

    ```
    composer install
    ```

3. Configure your `.env` file (database, mailer, etc).

4. Run migrations:

    ```
    php bin/console doctrine:migrations:migrate
    ```

5. Start the Symfony server:
    ```
    symfony server:start
    ```

## Deployment

-   Compatible with Alwaysdata and other PHP hosting platforms.
-   See [Symfony deployment docs](https://symfony.com/doc/current/deployment.html).

## License

MIT

## Author

Elsa Cruz-Mermy
# InnovShop
