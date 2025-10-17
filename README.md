# Vite + React + PHP (SQLite) SPA

A simple single-page app using **Vite + React (JS)** on the frontend and a lightweight **PHP + SQLite** backend API.

---

## 🚀 Quick Start

### 1. Install dependencies
```bash
cd frontend
npm install
```

### 2. Run backend (PHP API)
From the project root:
```bash
php -S 127.0.0.1:8000 -t backend/public backend/public/router.php
```

### 3. Run frontend (Vite dev)
In a new terminal:
```bash
cd frontend
npm run dev
```

Visit **http://localhost:5173**

---

## 🧩 Folder Structure
```
vite-react-php-spa/
├─ backend/
│  └─ public/
│     ├─ api/
│     │  └─ index.php        # PHP API
│     └─ router.php          # PHP dev router
├─ frontend/
│  ├─ index.html
│  ├─ vite.config.js
│  └─ src/
│     ├─ App.jsx
│     └─ main.jsx
└─ README.md
```

---

## 🧠 Build for Production
```bash
cd frontend
npm run build
cd ..
cp -r frontend/dist/* backend/public/
php -S 0.0.0.0:8000 -t backend/public backend/public/router.php
```

Then open **http://localhost:8000**

---

## ⚙️ Notes
- The PHP API uses SQLite automatically — no setup needed.
- All API routes live under `/api/*`.
- When adding a task, the app auto-reloads the list.

---

## 💡 Endpoints
| Method | Path | Description |
|:-------|:-----|:-------------|
| GET | `/api/tasks` | List tasks |
| POST | `/api/tasks` | Create task |
| PUT | `/api/tasks/{id}` | Update task |
| DELETE | `/api/tasks/{id}` | Delete task |

---

## 🧰 Scripts
| Command | Description |
|:--------|:-------------|
| `npm run dev` | Start Vite dev server |
| `npm run build` | Build for production |
| `php -S ...` | Run backend API |

---

Minimal. Fast. No frameworks beyond React + PHP.
