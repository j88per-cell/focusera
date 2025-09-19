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
