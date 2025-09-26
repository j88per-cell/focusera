# 

## About

**Refocus** is a self-hosted photography and art platform that empowers photographers and artists to present their work without being at the mercy of social media algorithms.  

Built on the popular **Laravel** framework, it can run on a wide variety of hosting providers. Popular cloud platforms such as **AWS**, **Hetzner**, and **DigitalOcean** are recommended.  

### Features

- 🎨 **Customizable Themes**  
  Show your artistic flair with fully customizable themes to control how your work is presented.  

- 🔐 **Passwordless Login (OTP)**  
  Simple, secure logins using one-time passwords. More secure and convenient than traditional passwords. And nothing to remember. 

- 🖼️ **Public & Private Galleries**  
  Share galleries via QR codes or short 6-digit codes, making it easy for clients, editors, and collaborators to access your work.  

- 🖨️ **Print Ordering with Pwinty**  
  Offer global, on-demand print fulfillment. Set your own markup and begin taking orders quickly.  


## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL/MariaDB (or a supported database)
- Redis (optional, used for cache/queue during development)

### Installation

```bash
git clone <repo>
cd photosite

composer install
cp .env.example .env
php artisan key:generate

# configure database credentials in .env

php artisan migrate
php artisan db:seed --class=Database\\Seeders\\SettingsFromConfigSeeder

npm install
npm run dev
```

The seeder populates:

- `site.theme.active` – active frontend theme (auto-detected in the admin UI)
- Feature toggles (news, featured galleries, sales/cart/order flow)
- Sales/provider placeholders so they can be edited from the admin without touching `.env`

### Configuration & Admin Setup

- Log in using the OTP flow (enter an email for an existing user; OTP is mailed via the configured driver).
- To create the initial admin account, browse directly to `/register` (no menu link). Registration emails an OTP-style verification code that must be entered to complete the setup.
- Navigate to **Admin → Settings** to manage:
  - **Site → theme → active**: theme selector auto-populates from `resources/js/Themes/*`.
  - **Site → photoproxy**: toggle PHP-based delivery for web-resolution images (thumbnails stay as direct `<img>` tags).
  - **Features**: toggle sales/cart, news, featured galleries.
  - **Sales**: choose provider, toggle sandbox, set API endpoints/keys.

When enabling the **Sales** feature, the Orders menu, cart API, and “Buy” buttons light up automatically via feature gating—no route cache flush needed.

---

## Code Style

- JavaScript style: terminating semicolons are required.
- Enforced via ESLint rule `semi: ["error", "always"]` and Prettier config `"semi": true`.
- Commands:
  - `npm run lint` – check for issues
  - `npm run lint:fix` – auto-fix where possible
  - `npm run format:write` – format using Prettier

Note: this repository does not include ESLint/Prettier devDependencies by default. To enable linting/formatting locally, install them:

```
npm i -D eslint prettier eslint-config-prettier eslint-plugin-vue vue-eslint-parser @vue/eslint-config-prettier
```

## Cleanup Task (Semicolons)

- Schedule a pass to run `npm run lint:fix` and `npm run format:write` to bring all files into compliance with the semicolon rule.
- Optionally add a pre-commit hook later (Husky + lint-staged) to keep the rule enforced on new commits.

## No JSX

- This project does not use JSX. ESLint is configured to disallow it (`ecmaFeatures.jsx = false` and `no-restricted-syntax` for `JSXElement`/`JSXFragment`).


## Contributing



## Code of Conduct



## Security Vulnerabilities


## License

This project is licensed under the **Elastic License 2.0 (ELv2)**.

You are free to use, modify, and self-host this software.  
You may **not** offer it as a hosted or managed service (SaaS) without explicit permission from the copyright holder.  

See the [LICENSE](./LICENSE) file for full details.
