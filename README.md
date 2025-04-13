# Modern E-Commerce Website

A fully functional e-commerce website built with PHP, MySQL, HTML, CSS, and JavaScript. This project features a modern, responsive design with a focus on user experience and functionality.

## Features

- User authentication (register, login, logout)
- Product browsing with filtering and search
- Shopping cart functionality
- Order management
- Responsive design for all devices
- Modern UI/UX with animations
- Secure user data handling
- Product categories and sorting
- Order confirmation and tracking

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository:

```bash
git clone https://github.com/yourusername/ecommerce-website.git
cd ecommerce-website
```

2. Create a MySQL database and import the schema:

```bash
mysql -u your_username -p your_database_name < database/schema.sql
```

3. Configure the database connection:

- Open `config/database.php`
- Update the database credentials:
  ```php
  define('DB_SERVER', 'localhost');
  define('DB_USERNAME', 'your_username');
  define('DB_PASSWORD', 'your_password');
  define('DB_NAME', 'your_database_name');
  ```

4. Set up your web server:

- Configure your web server to serve the `public` directory as the web root
- Ensure PHP has write permissions for session handling
- Enable URL rewriting if using Apache (mod_rewrite)

5. Create the required directories:

```bash
mkdir -p public/images/products
chmod 777 public/images/products
```

## Directory Structure

```
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── footer.php
│   └── navbar.php
├── public/
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── products.php
│   ├── cart.php
│   └── ...
└── README.md
```

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements for all database queries
- Input validation and sanitization
- CSRF protection
- Secure session handling
- XSS prevention

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Opera (latest)

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Font Awesome for icons
- Modern design inspiration from various e-commerce platforms
- PHP and MySQL communities for excellent documentation
