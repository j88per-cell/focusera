#

## About

**Focusera** is a self-hosted photography and art platform that empowers photographers and artists to present their work without being at the mercy of social media algorithms.

Built on the popular **Laravel** framework, it can run on a wide variety of hosting providers. Popular cloud platforms such as **AWS**, **Hetzner**, and **DigitalOcean** are recommended.

### Features

-   🎨 **Customizable Themes**  
    Show your artistic flair with fully customizable themes to control how your work is presented.

-   🔐 **Passwordless Login (OTP)**  
    Simple, secure logins using one-time passwords. More secure and convenient than traditional passwords. And nothing to remember.

-   🖼️ **Public & Private Galleries**  
    Share galleries via short 6-digit codes, making it easy to control who has access to specific galleries

-   🖨️ **Print Ordering with Print on Demand services**  
    On-demand print fulfillment. Set your own markup and begin taking orders quickly.

    **Nested galleries**
    You can nest galleries inside galleries as deep as you want.

    **Photo uploads**
    Automatically resize for thumbnail and web sizes defined in settings.

## Getting Started

### Prerequisites

-   PHP 8.2+
-   Composer
-   Node.js 18+
-   MySQL/MariaDB (or a supported database)
-   Redis (optional, used for improving site performance with caching)

### Installation

```bash
git clone <repo>
cd focusera

composer install
cp .env.example .env
php artisan key:generate

# configure database credentials in .env

php artisan migrate
php artisan db:seed

npm install
npm run dev
```

Seeding populates:

-   Core roles (Viewer, Contributor, Admin)
-   `site.theme.active` – active frontend theme (auto-detected in the admin UI)
-   Feature toggles (news, featured galleries, sales/cart/order flow)
-   Sales/provider placeholders so they can be edited from the admin without touching `.env`

### Configuration & Admin Setup

-   Log in using the OTP flow (enter an email for an existing user; OTP is mailed via the configured driver).
-   To create the initial admin account, browse directly to `/register` (no menu link). The very first user is automatically assigned the **Admin** role. After one additional user exists, self-registration is disabled and `/register` returns 403.
-   Ongoing user registration isn’t required—an admin can invite additional teammates (editors, photographers, etc.) via **Admin → Users** and assign roles on the fly.
-   Navigate to **Admin → Settings** to manage:
    -   **Site → theme → active**: theme selector auto-populates from `resources/js/Themes/*`.
    -   **Site → photoproxy**: toggle PHP-based delivery for web-resolution images (thumbnails stay as direct `<img>` tags).
    -   **Features**: toggle sales/cart, news, featured galleries.
    -   **Sales**: choose provider, toggle sandbox, set API endpoints/keys.
-   Use **Admin → Users** to invite teammates by email and assign roles—no passwords are required, invited users authenticate through the OTP flow.

When enabling the **Sales** feature, the Orders menu, cart API, and “Buy” buttons light up automatically via feature gating—no route cache flush needed.

---

## Contributing

## Code of Conduct

## Security Vulnerabilities

## License

This project is licensed under the **Elastic License 2.0 (ELv2)**.

You are free to use, modify, and self-host this software.  
You may **not** offer it as a hosted or managed service (SaaS) without explicit permission from the copyright holder.

See the [LICENSE](./LICENSE) file for full details.
