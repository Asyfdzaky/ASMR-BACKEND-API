# ASMR Backend API

This repository contains the backend API for an ASMR project , built using Laravel. It provides functionalities for managing residents (warga), RT/RW officials, letter submissions and approvals, and data reporting.

## Table of Contents

-   [Features](#features)
-   [Technologies Used](#technologies-used)
-   [Installation](#installation)
    -   [Prerequisites](#prerequisites)
    -   [Setup Steps](#setup-steps)
-   [API Endpoints](#api-endpoints)
    -   [Authentication](#authentication)
    -   [Wilayah (RT/RW)](#wilayah-rtrw)
    -   [Pejabat (Officials)](#pejabat-officials)
    -   [Pengajuan Surat (Letter Submissions)](#pengajuan-surat-letter-submissions)
    -   [Approval Surat (Letter Approvals)](#approval-surat-letter-approvals)
    -   [PDF Generation & Download](#pdf-generation--download)
    -   [Biodata (Resident Data)](#biodata-resident-data)
    -   [Approval Role (Admin)](#approval-role-admin)
    -   [Grafik (Charts)](#grafik-charts)
    -   [Program Kerja (Work Programs)](#program-kerja-work-programs)
-   [Testing](#testing)
-   [Contributing](#contributing)
-   [License](#license)

## Features

-   **User Authentication & Authorization**: Secure user registration, login, logout, password reset, and email verification. Supports roles: Admin, Warga (Resident), Pejabat RT, and Pejabat RW.
-   **Resident Management**: CRUD operations for resident data, including personal details and addresses. Account approval/rejection by admin.
-   **RT/RW Management**: Retrieve RT and RW data, with associated officials.
-   **Official Registration**: Dedicated registration, update, and deletion of RT/RW officials with signature upload.
-   **Letter Submission**: Residents can submit various types of letter applications.
-   **Letter Approval Workflow**: Multi-stage approval process for letters (RT -> RW).
-   **Dynamic PDF Generation**: Generate official letters in PDF format based on templates and submission data.
-   **PDF Download & Preview**: Allows downloading and previewing generated letters.
-   **Program Kerja Management**: CRUD operations for managing work programs.
-   **Data Analytics**: Endpoints for displaying summary counts and chart data (e.g., submissions per month, per type).

## Technologies Used

-   **Laravel**: PHP Framework
-   **PHP**: ^8.2
-   **Laravel Sanctum**: API Authentication
-   **Barryvdh/Laravel-DomPDF**: PDF generation
-   **Composer**: Dependency Manager
-   **MySQL/PostgreSQL/SQLite**: Database (configurable)

## Installation

### Prerequisites

-   PHP >= 8.2
-   Composer
-   A database (e.g., MySQL, PostgreSQL, SQLite)
-   Node.js & npm (if running frontend locally that interacts with this backend)

### Setup Steps

1.  **Clone the repository:**

    ```bash
    git clone [https://github.com/asyfdzaky/asmr-backend-api.git](https://github.com/asyfdzaky/asmr-backend-api.git)
    cd asmr-backend-api
    ```

2.  **Install Composer dependencies:**

    ```bash
    composer install
    ```

3.  **Create and configure your `.env` file:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Edit `.env` and configure your database connection (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Also, set `APP_URL` and `FRONTEND_URL` if you have a separate frontend application.

4.  **Run database migrations:**

    ```bash
    php artisan migrate
    ```

5.  **Seed the database (optional, but recommended for initial data like templates):**

    ```bash
    php artisan db:seed # This typically runs DatabaseSeeder
    php artisan db:seed --class=TemplateSuratSeeder # To add letter templates
    ```

6.  **Create a storage symlink (for uploaded files like signatures and PDFs):**

    ```bash
    php artisan storage:link
    ```

7.  **Start the Laravel development server:**
    ```bash
    php artisan serve
    ```
    The API will typically be available at `http://127.0.0.1:8000`.

## API Endpoints

All API endpoints are prefixed with `/api`. Unless specified, authentication with a Sanctum token is required (via `Authorization: Bearer <token>`).

### Authentication

-   `POST /api/register` - Register a new resident user.
-   `POST /api/login` - Authenticate a user and get a Sanctum token.
-   `POST /api/logout` - Invalidate the current user's token.
-   `POST /api/forgot-password` - Request a password reset link.
-   `POST /api/reset-password` - Reset password with token.
-   `GET /api/verify-email/{id}/{hash}` - Verify user's email.
-   `POST /api/email/verification-notification` - Resend email verification.
-   `GET /api/user` - Get the authenticated user's details.

### Wilayah (RT/RW)

-   `GET /api/wilayah/rw` - Get a list of all RWs.
-   `GET /api/wilayah/rt/{id_rw}` - Get a list of RTs belonging to a specific RW.

### Pejabat (Officials)

_(Requires `auth:sanctum` and `admin` middleware)_

-   `POST /api/pejabat/register` - Register a new RT/RW official.
-   `PUT /api/pejabat/{id}` - Update official's data by `warga_id`.
-   `DELETE /api/pejabat/{id}` - Delete official's data by `warga_id` (can also use `user_id` as query param `?user_id=X`).

### Pengajuan Surat (Letter Submissions)

_(Requires `auth:sanctum` middleware)_

-   `GET /api/surat/data-warga` - Get authenticated user's resident data for submission forms.
-   `POST /api/surat/pengajuan` - Submit a new letter application.
-   `GET /api/surat/riwayat-pengajuan` - Get recent letter submissions for the authenticated user.
-   `GET /api/surat/riwayat-prngajuan/{id_warga}` - Get detailed history of letter submissions for a specific resident.

### Approval Surat (Letter Approvals)

_(Requires `auth:sanctum` middleware)_

-   `GET /api/surat` - Get all letter submissions (can filter by `status`, `id_rt`, `id_rw`, `start_date`, `end_date`).
-   `GET /api/surat/pending/rt/{id_rt}` - Get pending letter submissions for a specific RT.
-   `GET /api/surat/pending/rw/{id_rw}` - Get pending letter submissions for a specific RW.
-   `PUT /api/surat/{id_pengajuan}/approval` - Update the approval status of a letter submission.

### PDF Generation & Download

_(Requires `auth:sanctum` middleware)_

-   `GET /api/surat/{pengajuan}/generate` - Generate a PDF for an approved letter submission.
-   `GET /api/surat/{pengajuan}/download` - Download a generated PDF letter.
-   `GET /api/surat/{pengajuan}/preview` - Preview a generated PDF letter in the browser.

### Biodata (Resident Data)

-   `GET /api/biodata` - Get all RT, RW, and resident data.
-   `GET /api/biodata/pending-warga` - Get residents with non-active status (pending admin approval).
-   `GET /api/biodata/count` - Get summary counts of residents, officials, and letter submissions.
-   `PUT /api/biodata/rt/{id}` - Update RT details and its associated official's data.
-   `PUT /api/biodata/rw/{id}` - Update RW details and its associated official's data.

### Approval Role (Admin)

_(Requires `auth:sanctum` and `admin` middleware)_

-   `GET /api/approval-role/warga` - Get all resident data for approval.
-   `PUT /api/approval-role/warga/{id}/approve` - Approve a resident's account.
-   `PUT /api/approval-role/warga/{id}/reject` - Reject a resident's account.

### Grafik (Charts)

-   `GET /api/grafik/jumlah-pengajuan-bulan` - Get the count of letter submissions per month for the current year (or a specified year).
-   `GET /api/grafik/jumlah-pengajuan-jenis` - Get the count of letter submissions grouped by type.

### Program Kerja (Work Programs)

-   `GET /api/proker` - Get all work programs.
-   `GET /api/proker/{id}` - Get a specific work program by ID.
-   `POST /api/proker` - Create a new work program.
-   `PUT /api/proker/{id}` - Update an existing work program.
-   `DELETE /api/proker/{id}` - Delete a work program.

## Testing

To run the tests for this project, use PHPUnit:

```bash
php artisan test
```
