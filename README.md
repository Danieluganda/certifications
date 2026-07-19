# CertPath 123

Laravel study app for certification preparation, built from the rules in `../rules`.

Repository: https://github.com/Danieluganda/certifications

## Local Setup

The app is configured for MySQL:

```env
DB_CONNECTION=mysql
DB_DATABASE=certifications_db
DB_USERNAME=root
DB_PASSWORD=your_local_password
```

Run the app:

```bash
php artisan serve --host=127.0.0.1 --port=8011
npm run dev
```

Seed login:

```text
learner@certpath.test
password
```
