# README.md

# Game Management Application

This project is a PHP-based Game Management application designed to handle various operations related to players and user accounts. It provides a user-friendly interface for managing player data, account information, and game-related functionalities.

## Project Structure

```
game-management
├── src
│   ├── config
│   │   └── database.php
│   ├── controllers
│   │   ├── PlayerController.php
│   │   └── AccountController.php
│   ├── models
│   │   ├── Player.php
│   │   └── Account.php
│   └── views
│       ├── addpontos.php
│       ├── ban.php
│       ├── buscachar.php
│       ├── buscaconta.php
│       ├── editaccount.php
│       ├── editarchar.php
│       ├── editarnome.php
│       ├── EnviarItens.php
│       ├── playerlogs.php
│       └── ress.php
├── public
│   └── index.php
├── .htaccess
└── README.md
```

## Features

- **Player Management**: Add, edit, delete, and retrieve player data.
- **Account Management**: Manage user accounts with CRUD operations.
- **User Interface**: Simple forms for various operations like adding points, banning players, and editing details.
- **Logging**: View player activity logs.

## Setup Instructions

1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Configure the database connection in `src/config/database.php`.
4. Set up a web server (e.g., Apache) and point it to the `public` directory.
5. Access the application via your web browser.

## Usage Guidelines

- Use the navigation to access different functionalities.
- Follow the prompts in each view to perform operations.
- Ensure that your database is properly configured to avoid connection issues.

## License

This project is licensed under the MIT License. See the LICENSE file for more details.