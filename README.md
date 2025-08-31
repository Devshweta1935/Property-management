# Property Management Backend

A Laravel-based REST API for managing real estate properties with agent authentication and email notifications.

## Features

- **Agent Management**: User registration, login, and authentication
- **Property CRUD**: Create, read, update, and delete properties
- **Ownership Control**: Agents can only manage their own properties
- **Email Notifications**: Automatic emails on property creation
- **Queue Ready**: Email system prepared for background processing
- **RESTful API**: Clean, standardized API endpoints

## Tech Stack

- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Database**: PostgreSQL 17
- **Authentication**: Laravel Sanctum
- **Email**: Laravel Mail with SMTP support
- **Queue**: Database-based job queues

## Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL 17
- Node.js & NPM (for frontend assets)

## Installation

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd property-management-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure environment variables**
   Edit `.env` file with your database and mail settings:
   ```env
   # PostgreSQL 17 Configuration
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=property_management
   DB_USERNAME=postgres
   DB_PASSWORD=your_postgres_password
   
   # Mail Configuration - Gmail SMTP
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=sv1829500@gmail.com
   MAIL_PASSWORD=mijobgbtqgyifqpw
   MAIL_ENCRYPTION=tls
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start the application**
   ```bash
   php artisan serve
   ```

## API Endpoints

### Authentication
- `POST /api/register` - Register a new agent
- `POST /api/login` - Login agent
- `POST /api/logout` - Logout agent (authenticated)

### Properties
- `GET /api/properties` - List authenticated agent's properties
- `POST /api/properties` - Create new property (authenticated)
- `GET /api/properties/{id}` - Get specific property (authenticated)
- `PUT /api/properties/{id}` - Update property (authenticated)
- `DELETE /api/properties/{id}` - Delete property (authenticated)
- `GET /api/my-properties` - Get all properties for authenticated agent

### Queue Monitoring
- `GET /api/queue/health` - Get overall queue health status
- `GET /api/queue/stats` - Get detailed queue statistics (use `?detailed=true` for more info)

## Testing the API

Use the provided Postman collection or .http file to test all endpoints.

### Sample Registration Request
```json
POST /api/register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "+1234567890",
    "company_name": "Real Estate Pro",
    "license_number": "RE123456"
}
```

### Sample Property Creation Request
```json
POST /api/properties
Authorization: Bearer {your-token}
{
    "title": "Beautiful Family Home",
    "description": "Spacious 3-bedroom home with garden",
    "address": "123 Main St",
    "city": "Anytown",
    "state": "CA",
    "zip_code": "90210",
    "price": 750000,
    "bedrooms": 3,
    "bathrooms": 2,
    "property_type": "house"
}
```

## Email Configuration

The application sends email notifications when properties are created. Gmail SMTP is pre-configured with the following settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=sv1829500@gmail.com
MAIL_PASSWORD=mijobgbtqgyifqpw
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=sv1829500@gmail.com
```

**Note**: The Gmail app password is already configured. Make sure to:
- Enable 2-factor authentication on your Gmail account
- Generate an app password specifically for this application
- Keep the app password secure and never commit it to version control

## Testing Email & Queue Functionality

### Testing Email System
To test the email system:

1. **Ensure your .env file has the Gmail credentials**
2. **Start queue workers**: `php artisan queue:work --queue=emails`
3. **Create a property through the API** - this will dispatch an email job to the queue
4. **Monitor the queue**: `php artisan queue:monitor --queue=emails`
5. **Check the email** sent to the agent's registered email address
6. **Verify email content** includes property details and professional formatting

### Testing Queue System
Test the queue functionality:

1. **Check queue health**: `php artisan queue:monitor`
2. **Monitor queue stats**: `GET /api/queue/stats?detailed=true`
3. **View failed jobs**: `php artisan queue:failed`
4. **Retry failed jobs**: `php artisan queue:retry all`

### Email Template Features
The email template includes:
- Professional styling with company branding
- Complete property details (address, price, features, etc.)
- Agent-specific information
- Responsive design for mobile and desktop

## Queue Configuration

Emails are queue-ready. To process queues:

```bash
# Get queue startup instructions
php artisan queue:start

# Process all queues
php artisan queue:work

# Process only email queue
php artisan queue:work --queue=emails

# Process with verbose output
php artisan queue:work --verbose
```

## Database Schema

- **Users**: Agent information with authentication
- **Properties**: Property details with agent ownership
- **Personal Access Tokens**: Sanctum authentication tokens
- **Jobs**: Queue job management
- **Failed Jobs**: Failed job tracking

## PostgreSQL 17 Setup

1. **Install PostgreSQL 17**:
   - Windows: Download from [PostgreSQL Downloads](https://www.postgresql.org/download/)
   - Ubuntu/Debian: `sudo apt-get install postgresql-17`
   - CentOS/RHEL: `sudo yum install postgresql17-server`
   - macOS: `brew install postgresql@17`

2. **Create Database**:
   ```bash
   sudo -u postgres createdb property_management
   ```

3. **Configure .env**:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=property_management
   DB_USERNAME=postgres
   DB_PASSWORD=your_postgres_password
   ```

## Security Features

- JWT-like tokens via Laravel Sanctum
- Ownership validation for all property operations
- Input validation and sanitization
- Soft deletes for data integrity
- Proper HTTP status codes

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
