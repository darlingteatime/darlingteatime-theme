# Darling Teatime: Local Development Guide

This is a premium local development environment for the **Darling Teatime** child theme and WooCommerce website. 

The environment runs natively inside **WSL2 (Ubuntu)**, which delivers maximum performance (instant page loads).

---

## 🚀 Environment Coordinates

| Resource | URL | Credentials |
| :--- | :--- | :--- |
| **Local WordPress Site** | `http://localhost:8888` | *View front-page & shop* |
| **WordPress Admin Dashboard** | `http://localhost:8888/wp-admin` | Username: **`admin`** <br> Password: **`password`** |
| **WordPress Test Site** | `http://localhost:8889` | *Used for automated testing* |

---

## 🌐 Live Web Sandbox Preview (WebAssembly)

This project has a built-in **WordPress Playground Blueprint** (`blueprint.json`) that allows anyone to view a live, fully working preview of this theme directly in their web browser without installing anything!

### How to use it:
To preview the `main` branch, simply open this URL:
`https://playground.wordpress.org/?blueprinturl=https://raw.githubusercontent.com/japek/darlingteatime-theme/main/blueprint.json`

### Previewing other branches:
If you want to preview a different active branch (e.g. `feature-theme-updates`), construct the URL by swapping `main` for your branch name:
`https://playground.wordpress.org/?blueprinturl=https://raw.githubusercontent.com/japek/darlingteatime-theme/<your-branch-name>/blueprint.json`
*(Note: Ensure you update the theme zip URL inside `blueprint.json` to point to the correct branch ZIP on GitHub as well!).*

---

## 🛠️ Management Commands

Run these commands inside your **Ubuntu WSL terminal** (ensure you are in the project folder `~/Github/darlingteatime-theme`):

### 1. Start the Environment
```bash
npm run env:start
```
*Spins up the Docker containers natively inside WSL. Any code changes you make are instantly visible in your browser.*

### 2. Stop the Environment
```bash
npm run env:stop
```
*Stops the active containers without destroying your database.*

### 3. Re-Import / Seed Sample Data
```bash
npm run env:seed
```
*Resets theme/plugin states and programmatically pulls WooCommerce's sample product CSV (25 items including teas, mugs, clothing, etc.) directly into your local database using the custom script located in `bin/seed-products.php`.*

### 4. Run WP-CLI Commands
```bash
npm run env:cli -- <command>
# Example: List active plugins
npm run env:cli -- plugin list
```

### 5. Clean Database & Reset
```bash
npm run env:clean
```
*Resets the environment completely back to a fresh state (warning: destroys local database content).*

---

## 📝 Troubleshooting & Optimization Notes

- **Blazing Fast I/O:** By running this project directly inside Ubuntu (`~/Github/...`) rather than the Windows filesystem, you completely bypass the slow Windows-to-Linux translation layer. Response times are reduced from seconds to **~0.6s**!
- **VS Code Integration:** To edit code natively inside WSL with zero latency, open your Ubuntu terminal, navigate to this directory, and type:
  ```bash
  code .
  ```
  This will open VS Code on Windows but run its background server inside WSL, giving you native file access and smooth editing.
