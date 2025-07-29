# Lock Early Submit - Moodle Local Plugin

**Plugin name**: `local_lockearlysubmit`  
**Purpose**: Automatically lock assignment submissions made *before* the due date once the due date has passed, to prevent students from overriding early submissions. Also unlocks submissions if an extension is granted.

---

## 📌 Features

- 🚫 Locks assignment submissions made before the due date as soon as the due date passes.
- 🔓 Automatically unlocks submissions if an extension is granted by a teacher.
- ♻️ Re-locks if the extension is removed after it was granted.
- 🗑️ Unlocks if a submission is deleted, allowing students to resubmit.
- 🔒 Ensures students can't unintentionally overwrite early submissions after the deadline.
- ✅ No cron/scheduled task needed — purely event-based using Moodle’s Event API.

---

## 📁 Installation

1. Copy or clone this plugin into your Moodle installation at:

2. Visit the Site Administration in your browser to trigger the installation.

3. Done! The plugin works silently in the background — no configuration required.

---

## ⚙️ How It Works

- **Locks Submissions**: When the due date passes, all submissions submitted *before* the due date will be locked.
- **Unlocks on Extension**: If a student is given an extension, their submission will be unlocked.
- **Handles Deletions**: If a submission is deleted (e.g., by a teacher), it unlocks the slot for the student.

---

## 🛡️ Privacy

This plugin **does not store any personal data**. It uses only assignment and submission metadata and complies with Moodle’s Privacy API.

See: [`classes/privacy/provider.php`](./classes/privacy/provider.php)

---

## 👤 Author

- **Name**: Amit Bhardwaj
- **Email**: moodlebyamit@gmail.com  
- **Year**: 2025  
- **License**: GNU GPL v3 or later  
- **Website**: [https://moodle.org](https://moodle.org)

---

## 📄 License

This plugin is licensed under the terms of the [GNU General Public License v3](http://www.gnu.org/copyleft/gpl.html).

