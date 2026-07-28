# AI-Powered Study Planner

## Overview
AI-Powered Study Planner is a PHP web application that helps students manage subjects, generate an intelligent study schedule, track progress, and review plans through timeline, calendar, reminder, and export views.

This version now uses:
- MySQL for real data storage
- PHP sessions for login state and flash messages
- Browser reminders
- Login and registration
- Subject editing and deleting
- Calendar view
- Print-to-PDF export

---

## Technologies
- HTML5
- CSS3
- Vanilla JavaScript
- PHP
- MySQL
- XAMPP

---

## Main Features
- Register and log in
- Add subjects with difficulty and exam date
- Edit and delete subjects
- Save daily study hours in MySQL
- Generate a study plan using AI-style priority logic
- View the plan in timeline format
- View the plan in calendar format
- Mark tasks as completed
- Track progress by subject and overall
- Enable browser reminders
- Export the plan as PDF through the browser print dialog
- Responsive layout with dark mode

---

## AI Logic
The study plan uses this formula:

```text
Priority Score = (Difficulty x Weight) / Days Left
```

### Difficulty values
- Easy = 1
- Medium = 2
- Hard = 3

### Weight
- The app uses a constant value of `10`

### Meaning
- Harder subjects get more priority
- Subjects with closer exam dates get more priority
- The planner gives more study hours to the subjects with higher scores

---

## Folder Structure

```text
study-planner/
|-- README.md
|-- config.php
|-- database.sql
|-- index.php
|-- css/
|   `-- style.css
|-- includes/
|   |-- footer.php
|   |-- functions.php
|   `-- header.php
|-- js/
|   `-- app.js
`-- pages/
    |-- add_subject.php
    |-- calendar.php
    |-- edit_subject.php
    |-- export_plan.php
    |-- generate_plan.php
    |-- login.php
    |-- logout.php
    |-- progress.php
    |-- register.php
    `-- view_plan.php
```

---

## Database

### Suggested database name
```text
study_planner_db
```

### Tables used
- `users`
- `subjects`
- `study_plan`

### Extra user settings stored in MySQL
- `daily_study_hours`
- `browser_reminders_enabled`
- `reminder_time`

The latest schema is in:

```text
database.sql
```

---

## How to Run

1. Put the project folder inside:

```text
C:\xampp\htdocs\study-planner
```

2. Start `Apache` and `MySQL` from XAMPP

3. Make sure the database exists:

```text
study_planner_db
```

4. Import:

```text
database.sql
```

5. Open:

[http://localhost/study-planner/pages/register.php](http://localhost/study-planner/pages/register.php)

6. Create an account and start using the app

---

## Default Database Connection
The app is configured for standard XAMPP settings:

```text
Host: localhost
User: root
Password:
Database: study_planner_db
```

These values are defined in:

```text
config.php
```

---

## User Manual

## 1. Register
Open the register page and create an account using:
- full name
- email
- password

## 2. Login
Log in with the email and password you created.

## 3. Set study preferences
On the dashboard:
- set your daily study hours
- choose reminder time
- enable browser reminders if needed

## 4. Add subjects
Use the Add Subject page and enter:
- subject name
- difficulty
- exam date

## 5. Edit or delete subjects
On the dashboard, each subject card has:
- Edit
- Delete

When a subject changes, the study plan is refreshed automatically.

## 6. Generate the plan
Open the Generate Plan page and click:

```text
Generate Study Plan
```

The plan is saved into MySQL.

## 7. View the plan
Use the View Plan page to see the daily study timeline.

## 8. Open calendar view
Use the Calendar page to see tasks grouped by date in a monthly layout.

## 9. Track progress
Use the Progress page to:
- mark tasks as completed
- move completed tasks back to pending
- track progress percentages

## 10. Export to PDF
Open the Export PDF option from the dashboard or plan view.

The browser print dialog opens, and you can choose:

```text
Save as PDF
```

---

## File Purpose

### `config.php`
- starts the session
- defines constants
- loads shared helpers

### `includes/functions.php`
- MySQL connection
- authentication
- subject CRUD
- planner logic
- progress logic
- reminder payload generation
- calendar data generation

### `includes/header.php`
- shared layout
- top navigation
- hero section
- mobile navigation

### `includes/footer.php`
- shared footer
- JavaScript include

### `index.php`
- main dashboard
- preferences form
- alerts
- subject management

### `pages/login.php`
- user login

### `pages/register.php`
- user registration

### `pages/logout.php`
- sign out

### `pages/add_subject.php`
- add a subject

### `pages/edit_subject.php`
- update a subject

### `pages/generate_plan.php`
- preview formula and generate schedule

### `pages/view_plan.php`
- timeline view of the schedule

### `pages/calendar.php`
- monthly calendar view

### `pages/progress.php`
- progress and task completion page

### `pages/export_plan.php`
- print-friendly export page

### `js/app.js`
- dark mode
- mobile nav
- frontend validation
- delete confirmation
- browser reminders

### `css/style.css`
- layout
- responsiveness
- dark mode
- cards, buttons, calendar, auth UI

---

## Data Flow

## Registration and login
1. User submits the form
2. PHP validates input
3. User data is checked or inserted in MySQL
4. The session stores the logged-in user id

## Subject management
1. User adds or edits a subject
2. PHP validates the values
3. MySQL stores the change
4. The planner refreshes automatically

## Plan generation
1. PHP loads subjects from MySQL
2. Priority scores are calculated
3. Daily study hours are distributed
4. The new plan is written into `study_plan`

## Progress
1. User changes task status
2. PHP updates `study_plan.status`
3. Progress values are recalculated from stored rows

---

## Browser Reminders
Browser reminders work with the Notification API.

How it works:
- user enables reminders in dashboard settings
- user allows browser notification permission
- the app checks today's pending tasks
- the browser shows a reminder after the selected reminder time

Note:
- browser reminders work best on supported browsers
- they are not email reminders

---

## PDF Export
PDF export is handled through the browser print dialog.

How it works:
- open Export PDF
- the app opens a print-friendly page
- the browser print window opens automatically
- choose `Save as PDF`

This avoids needing external PDF libraries.

---

## Troubleshooting

### App shows database errors
Check:
- MySQL is running in XAMPP
- the database name is `study_planner_db`
- tables were created from `database.sql`

### Login or registration does not work
Check:
- `users` table exists
- email values are unique

### Subjects are not saving
Check:
- user is logged in
- MySQL is running
- `subjects` table exists

### Plan is not generating
Check:
- at least one subject exists
- daily study hours is greater than 0
- exam dates are valid

### Browser reminders do not appear
Check:
- browser notifications are allowed
- the reminder time has passed
- there are pending tasks for today or urgent exams

### PDF does not save
Use the browser print dialog and select:

```text
Save as PDF
```

---

## Current Status
- MySQL connected
- Login and registration added
- Subject editing and deleting added
- Calendar view added
- Browser reminders added
- PDF export added
- Responsive design retained

---

## Possible Next Improvements
- email reminders with server mail configuration
- forgot password flow
- subject notes and attachments
- drag-and-drop calendar
- exam countdown widget
- analytics dashboard
