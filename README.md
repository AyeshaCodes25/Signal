# Signal 📡✅
A full-stack task management platform styled as a mission-control log, 
built to demonstrate a complete PHP + MySQL backend paired with a 
Tailwind CSS and vanilla JavaScript frontend.
## 🌐 Live Demo
🔗 https://signal.infinityfree.me
## 📋 About the Project
Signal lets users log in, create and track tasks with status and 
priority, and monitor progress from a live dashboard. Every task is 
treated like a control-room log entry — stamped with a status badge, 
a ticket ID, and a due date — and updates happen instantly through a 
JSON API with no page reloads. The dashboard pulls its stats directly 
from MySQL, giving a real-time view of completion rate, overdue 
items, and recent activity.
## 📸 Screenshots
| Sign In | Control Room |
|-----------|-------------|
| ![Sign In](signin.JPG) | ![Control Room](controlroom.JPG) |
| Task Log | New Entry |
|-----------|---------|
| ![Task Log](tasklog.JPG) | ![New Entry](newentry.JPG) |
## ✨ Key Features
### 🔐 Authentication
- Registration with server-side validation
- Secure login with bcrypt password hashing
- Session-based access control on every protected page and API route
### 📋 Task Log
- Create, edit, and delete tasks in real time via a JSON API
- Set status (pending / in progress / completed) and priority (low / medium / high / urgent)
- Assign and track due dates, with overdue items flagged automatically
- Live search across titles and descriptions
- Filter by status or priority, and sort by due date, priority, or newest
### 📊 Control Room Dashboard
- Total tasks, in-progress count, completed count, and overdue count
- Completion rate calculated straight from MySQL aggregate queries
- Upcoming tasks by due date
- Recent activity feed
## 🛠️ Technologies Used
### Frontend
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=flat&logo=tailwindcss&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
### Backend
![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=flat&logo=xampp&logoColor=white)
## 🚀 Getting Started
1. Clone the repo and place it in your local server directory (e.g. `htdocs`)
2. Import the schema: `mysql -u root -p < schema.sql`
3. Update credentials in `config/db.php` if needed
4. Visit the project in your browser and sign in

Full setup instructions are in the repo.
## 🔐 Demo Login Credentials
- **Email:** demo@signal.dev
- **Password:** password123
---
## 👩‍💻 Developer Contact
**Ayesha Amjad** — Front-End Developer & Digital Marketing Specialist
📧 ayeshaamjad819@gmail.com
🌐 Live Project: https://signal.infinityfree.me
🔗 github.com/AyeshaCodes25

---

## 🏫 Institution
📍 GC University Faisalabad — Department of Information Technology
