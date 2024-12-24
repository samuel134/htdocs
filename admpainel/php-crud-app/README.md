# PHP CRUD Application

This is a simple PHP CRUD application that allows administrators to manage users and characters in a gaming environment. The application provides various functionalities such as adding points, banning users, searching for characters and accounts, editing account and character details, sending items, viewing player logs, and resurrecting characters.

## Project Structure

```
php-crud-app
├── src
│   ├── addpontos.php        # Handles addition of points to a user
│   ├── ban.php              # Manages banning of users
│   ├── buscachar.php        # Searches for characters in the database
│   ├── buscaconta.php       # Searches for user accounts
│   ├── editaccount.php      # Edits user account details
│   ├── editarchar.php       # Edits character details
│   ├── editarnome.php       # Renames characters
│   ├── EnviarItens.php      # Manages sending of items between users
│   ├── playerlogs.php       # Displays logs of player activities
│   └── ress.php             # Handles resurrection of characters
├── index.php                # Main entry point for the application
├── config.php               # Configuration settings for database connections
└── README.md                # Documentation for the project
```

## Installation

1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Configure the `config.php` file with your database connection settings.
4. Ensure your web server is set up to serve the application.

## Usage

- Access the application through your web browser by navigating to `index.php`.
- Use the navigation links to access different functionalities of the application.
- Follow the on-screen instructions for each operation.

## Contributing

Feel free to fork the repository and submit pull requests for any improvements or bug fixes. 

## License

This project is open-source and available under the MIT License.