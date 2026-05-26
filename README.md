# 🧠 Science Literacy & Temper Platform

A highly interactive, gamified web application built using **Laravel 12** and **MongoDB Atlas** that fosters scientific thinking, debunks common myths, and evaluates scientific literacy using state-of-the-art **Gemini 2.5 Flash** models.

---

## ✨ Features

### 1. 🎓 Scientific Temper Quiz
* Dynamic, interactive multiple-choice quizzes designed to test logical, scientific, and empirical thinking.
* Evaluates scores in real-time and awards gamified XP directly to user profiles.
* **🧠 Ask AI Explainer**: Click **Ask AI** on any wrong answer to get a personalized, deep explanation generated instantly by Gemini 2.5 Flash explaining *why* your answer was incorrect and *why* the correct answer is true.
* **✨ AI Scientific Analysis**: At the end of every quiz, Gemini AI writes a personalized report analyzing common misconceptions based on wrong answers and offering advice on how to think like a scientist.

### 2. 🤖 Persistent AI Science Tutor
* A custom interactive slide-out chat widget featuring a friendly AI Science Tutor.
* **Persistent Conversations**: Every chat message and AI reply is saved directly to a dedicated `chat_logs` collection in MongoDB.
* **History Hydration**: On loading the widget, your last 20 messages are fetched and rendered chronologically, persisting your learning journey across page reloads.

### 3. 🕵️‍♂️ Scientific Mythbusters
* Dynamically debunks 5 popular scientific misconceptions.
* Powered by the Gemini API with a robust regular expression JSON parser ensuring smooth LLM data parsing.
* **12-Hour Cache Optimization**: To optimize performance and API quota usage, the myths are cached dynamically for **exactly 12 hours** in accordance with modern Laravel standards.

### 4. 🏆 Gamified Leaderboard
* Users gain XP for scoring well on quizzes.
* Displays global high scores and standings in a beautifully styled Leaderboard.

### 5. 🔒 Industry-Grade Security Audit Logs
* Captures all authentication actions securely in MongoDB (`audit_logs` collection):
  - `USER_REGISTERED`: Logged when a new account is created.
  - `LOGIN_SUCCESS`: Logged upon successful user access.
  - `LOGIN_FAILED`: Logged with targeted emails on unsuccessful attempts (vital for identifying brute force attacks).
  - `LOGOUT`: Logged when sessions terminate.
  - Automatically captures client metadata (IP address and User Agent).

---

## 🛠️ Technology Stack
* **Framework**: Laravel 12 (PHP 8.2+)
* **Database**: MongoDB Atlas (Cloud) & SQLite (local sessions/cache)
* **Frontend**: Vanilla CSS, Blade Templates, and Javascript (Vite compilation)
* **AI Model**: Google Gemini 2.5 Flash API

---

## 🚀 Local Installation & Setup

### 1. Prerequisites
Ensure you have the following installed:
* **PHP** (Version 8.2 or higher)
* **Composer** (PHP dependency manager)
* **Node.js** (Version 18 or higher) & **NPM**

### 2. Configure Environment (`.env`)
1. Duplicate `.env.example` to `.env`:
   ```bash
   copy .env.example .env
   ```
2. Configure the environment variables inside `.env`:
   ```env
   # MongoDB Atlas Connection URI
   MONGODB_URI=mongodb+srv://<username>:<password>@cluster0.xxxx.mongodb.net/sci_literacy?retryWrites=true&w=majority&appName=Cluster

   # Google Gemini API Key
   GEMINI_API_KEY=AIzaSy...

   # Session Driver (Highly recommended to keep as file for seamless Atlas session storage)
   SESSION_DRIVER=file
   ```

### 3. Build Assets and Install Packages
Installs all backend/frontend packages, generates app keys, runs SQLite migrations, and builds compiled front-end assets:
```bash
composer run setup
```

### 4. Seed the Databases
Seeds default administration and scientific quiz questions directly into both SQLite and MongoDB Atlas:
```bash
php artisan db:seed
```

### 5. Start the Application
Launches your local PHP server, asset compiler, and logging systems concurrently:
```bash
composer run dev
```
Open **[http://127.0.0.1:8000](http://127.0.0.1:8000)** in your browser.

* **Seeded Admin Credentials**:
  * **Email**: `admin@admin.com`
  * **Password**: `password`

---

## ☁️ Production Deployment Guide

Deploying this Laravel-MongoDB-Gemini stack is incredibly simple and cost-free on modern cloud platforms (like **Render**, **Railway**, or **fly.io**).

### 1. Set Up Free MongoDB Atlas Cloud Database
1. Create a free account at [MongoDB Atlas](https://www.mongodb.com/cloud/atlas).
2. Deploy a free **M0 cluster** in a region close to your user base.
3. In **Database Access**, create a user with a secure password and standard read/write permissions.
4. In **Network Access**, add `0.0.0.0/0` to allow connections from your cloud deployment servers.
5. Click **Connect** -> **Drivers**, select your connection URI, and append `/sci_literacy` before the query parameters.

### 2. Deploy on Render / Railway
1. Push this codebase to a private/public GitHub repository.
2. Link your repository to a new **Web Service** on Render or Railway.
3. Choose the **PHP / Laravel** or **Docker** configuration.
4. Add the following **Environment Variables** in the cloud platform's dashboard:
   - `APP_ENV`: `production`
   - `APP_DEBUG`: `false`
   - `APP_KEY`: *(Generate locally using `php artisan key:generate --show`)*
   - `APP_URL`: Your deployed website URL (e.g. `https://sci-literacy.onrender.com`)
   - `DB_CONNECTION`: `sqlite`
   - `SESSION_DRIVER`: `file`
   - `MONGODB_URI`: *Your MongoDB Atlas connection string*
   - `GEMINI_API_KEY`: *Your Google Gemini API Key*
5. Set the **Build Command** to:
   ```bash
   composer install --no-dev --optimize-autoloader && npm install && npm run build
   ```
6. Set the **Start Command** to:
   ```bash
   php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=$PORT
   ```

Your platform will automatically build, seed, configure session handlers, and serve live production traffic!
