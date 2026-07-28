<?php
/**
 * index.php
 *
 * Authenticated dashboard backed by MySQL.
 */

require_once __DIR__ . '/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        addFlashMessage('danger', 'Session expired or invalid CSRF token. Please try again.');
        redirect('/index.php');
    }

    if ($action === 'save_preferences') {
        $dailyHours = isset($_POST['daily_hours']) ? (float) $_POST['daily_hours'] : 0;
        $reminderTime = $_POST['reminder_time'] ?? substr(DEFAULT_REMINDER_TIME, 0, 5);
        $browserEnabled = isset($_POST['browser_reminders_enabled']);
        $plannerWeight = isset($_POST['planner_weight']) ? (int) $_POST['planner_weight'] : 10;
        $maxHours = isset($_POST['max_hours_per_subject']) ? (float) $_POST['max_hours_per_subject'] : 4.0;

        if ($dailyHours < 0.5 || $dailyHours > 16) {
            addFlashMessage('danger', 'Daily study hours must be between 0.5 and 16.');
        } elseif ($plannerWeight < 1 || $plannerWeight > 20) {
            addFlashMessage('danger', 'Priority weight must be between 1 and 20.');
        } elseif ($maxHours < 0.5 || $maxHours > 16) {
            addFlashMessage('danger', 'Max hours per subject must be between 0.5 and 16.');
        } else {
            saveUserSettings($dailyHours, $browserEnabled, $reminderTime, $plannerWeight, $maxHours);
            addFlashMessage('success', 'Settings saved successfully.');
        }

        redirect('/index.php');
    }

    if ($action === 'delete_subject') {
        $subjectId = isset($_POST['subject_id']) ? (int) $_POST['subject_id'] : 0;

        if ($subjectId > 0 && deleteSubjectRecord($subjectId)) {
            addFlashMessage('success', 'Subject deleted and study plan refreshed.');
        } else {
            addFlashMessage('danger', 'Unable to delete that subject.');
        }

        redirect('/index.php');
    }
}

$subjects = getSubjects();
$studyPlan = getStudyPlan();
$subjectCards = buildSubjectCards($subjects, $studyPlan);
$alerts = buildDashboardAlerts($subjects, $studyPlan, getDailyHours());
$overallProgress = getOverallProgress($studyPlan);
$nextStudySession = getNextStudySession($studyPlan);
$highestPriorityTask = getHighestPriorityTask($studyPlan);
$plannedDays = countPlannedDays($studyPlan);
$totalTasks = count($studyPlan);
$completedTasks = count(array_filter($studyPlan, function (array $planItem): bool {
    return $planItem['status'] === 'completed';
}));
$reminderSettings = getReminderSettings();
$reminderPayload = buildReminderPayload($studyPlan, $subjectCards);

$userRecord = getCurrentUser();
$plannerWeight = $userRecord ? $userRecord['planner_weight'] : APP_WEIGHT;
$maxHoursPerSubject = $userRecord ? $userRecord['max_hours_per_subject'] : 4.0;

$upcomingExams = array_filter($subjects, function($s) {
    return calculateDaysLeft($s['exam_date']) > 0;
});
$nextExamSubject = !empty($upcomingExams) ? array_values($upcomingExams)[0] : null;

$pageTitle = APP_NAME . ' | Dashboard';
$pageHeading = 'Dashboard';
$pageDescription = 'Manage your study settings, keep subjects organized, and stay on top of the latest plan.';

require_once __DIR__ . '/includes/header.php';
?>

<section class="stats-grid">
    <article class="card stat-card">
        <p class="stat-label">Subjects</p>
        <h2><?= count($subjects) ?></h2>
        <p class="stat-meta">Subjects connected to your account.</p>
    </article>

    <article class="card stat-card">
        <p class="stat-label">Daily Hours</p>
        <h2><?= formatHours(getDailyHours()) ?></h2>
        <p class="stat-meta">Saved in MySQL and used in plan generation.</p>
    </article>

    <article class="card stat-card progress-ring-stat">
        <div class="progress-ring-container" style="--progress-percent: <?= $overallProgress ?>;">
            <svg class="progress-ring" width="80" height="80">
                <circle class="progress-ring-bg" stroke="var(--border)" stroke-width="6" fill="transparent" r="32" cx="40" cy="40"/>
                <circle class="progress-ring-bar" stroke="var(--accent)" stroke-width="6" fill="transparent" r="32" cx="40" cy="40"/>
            </svg>
            <div class="progress-ring-text">
                <h3><?= $overallProgress ?>%</h3>
            </div>
        </div>
        <div>
            <p class="stat-label">Overall Progress</p>
            <p class="stat-meta"><?= $completedTasks ?> of <?= $totalTasks ?> tasks completed.</p>
        </div>
    </article>

    <article class="card stat-card">
        <p class="stat-label">Planned Days</p>
        <h2><?= $plannedDays ?></h2>
        <p class="stat-meta">Days currently covered by your schedule.</p>
    </article>
</section>

<section class="split-layout">
    <article class="card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Settings</p>
                <h2>Study Preferences</h2>
            </div>
        </div>

        <form method="post" class="form-grid" data-hours-form>
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_preferences">

            <div class="input-group">
                <label for="daily_hours">Daily study hours</label>
                <input
                    id="daily_hours"
                    name="daily_hours"
                    type="number"
                    min="0.5"
                    max="16"
                    step="0.5"
                    value="<?= e(formatHours(getDailyHours())) ?>"
                    required
                >
            </div>

            <div class="input-group">
                <label for="reminder_time">Reminder time</label>
                <input
                    id="reminder_time"
                    name="reminder_time"
                    type="time"
                    value="<?= e($reminderSettings['reminder_time']) ?>"
                >
            </div>

            <div class="input-group">
                <label for="planner_weight">Priority weight (Difficulty vs Urgency): <span id="weight_val"><?= $plannerWeight ?></span></label>
                <input
                    id="planner_weight"
                    name="planner_weight"
                    type="range"
                    min="1"
                    max="20"
                    step="1"
                    value="<?= $plannerWeight ?>"
                    oninput="document.getElementById('weight_val').innerText = this.value"
                    required
                >
            </div>

            <div class="input-group">
                <label for="max_hours_per_subject">Max daily hours per subject</label>
                <input
                    id="max_hours_per_subject"
                    name="max_hours_per_subject"
                    type="number"
                    min="0.5"
                    max="16"
                    step="0.5"
                    value="<?= e(formatHours($maxHoursPerSubject)) ?>"
                    required
                >
            </div>

            <label class="checkbox-row full-width">
                <input
                    type="checkbox"
                    name="browser_reminders_enabled"
                    value="1"
                    <?= $reminderSettings['browser_enabled'] ? 'checked' : '' ?>
                >
                <span>Enable browser reminders</span>
            </label>

            <div class="form-actions full-width">
                <button class="button" type="submit">Save Settings</button>
                <button class="button button-secondary" type="button" data-request-notifications>Allow Notifications</button>
            </div>

            <p class="support-text full-width" data-notification-status>Browser notification status will appear here.</p>
        </form>
    </article>

    <article class="card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Action Center</p>
                <h2>Focus Snapshot</h2>
            </div>
        </div>

        <?php if ($nextStudySession === null): ?>
            <div class="empty-inline">
                <p>No upcoming session yet. Generate a plan to create your schedule.</p>
            </div>
        <?php else: ?>
            <div class="focus-card">
                <div>
                    <p class="focus-label"><?= e($nextStudySession['subject_name']) ?></p>
                    <h3><?= formatHours((float) $nextStudySession['recommended_hours']) ?> hours on <?= e(formatFriendlyDate($nextStudySession['date'])) ?></h3>
                    <p>Priority <?= e(number_format((float) $nextStudySession['priority_score'], 2)) ?> | Exam <?= e(formatFriendlyDate($nextStudySession['exam_date'])) ?></p>
                </div>
                <span class="badge badge-warning"><?= ucfirst(e($nextStudySession['status'])) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($nextExamSubject !== null): ?>
            <div class="countdown-widget" data-countdown-target="<?= e($nextExamSubject['exam_date']) ?>T09:00:00">
                <p class="countdown-label">Days until <strong><?= e($nextExamSubject['name']) ?></strong> exam:</p>
                <div class="countdown-timer">
                    <div class="countdown-item"><span id="cd-days">00</span><label>Days</label></div>
                    <div class="countdown-item"><span id="cd-hours">00</span><label>Hours</label></div>
                    <div class="countdown-item"><span id="cd-mins">00</span><label>Mins</label></div>
                    <div class="countdown-item"><span id="cd-secs">00</span><label>Secs</label></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card-actions">
            <a class="button" href="<?= e(url('/pages/generate_plan.php')) ?>">Refresh Plan</a>
            <a class="button button-secondary" href="<?= e(url('/pages/export_plan.php')) ?>" target="_blank" rel="noopener">Export PDF</a>
        </div>

        <?php if ($highestPriorityTask !== null): ?>
            <p class="support-text">
                Highest priority right now: <strong><?= e($highestPriorityTask['subject_name']) ?></strong>
            </p>
        <?php endif; ?>
    </article>
</section>

<section class="split-layout">
    <article class="card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Alerts</p>
                <h2>Planner Signals</h2>
            </div>
        </div>

        <?php if (empty($alerts)): ?>
            <div class="empty-inline">
                <p>No alerts right now.</p>
            </div>
        <?php else: ?>
            <div class="alert-stack">
                <?php foreach ($alerts as $alert): ?>
                    <div class="alert-card alert-<?= e($alert['type']) ?>">
                        <strong><?= ucfirst(e($alert['type'])) ?>:</strong>
                        <span><?= e($alert['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>

    <article class="card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Navigation</p>
                <h2>Quick Links</h2>
            </div>
        </div>

        <div class="list-stack">
            <a class="list-card quick-link" href="<?= e(url('/pages/add_subject.php')) ?>">
                <strong>Add a new subject</strong>
                <p>Create another subject and refresh the plan automatically.</p>
            </a>
            <a class="list-card quick-link" href="<?= e(url('/pages/calendar.php')) ?>">
                <strong>Open calendar view</strong>
                <p>See your plan in a month-by-month layout.</p>
            </a>
            <a class="list-card quick-link" href="<?= e(url('/pages/progress.php')) ?>">
                <strong>Update progress</strong>
                <p>Mark completed tasks and watch your percentages change.</p>
            </a>
        </div>
    </article>
</section>

<section class="section-block">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Subjects</p>
            <h2>Manage Subjects</h2>
        </div>
        <a class="button" href="<?= e(url('/pages/add_subject.php')) ?>">Add Subject</a>
    </div>

    <?php if (empty($subjectCards)): ?>
        <article class="card empty-state">
            <h3>No subjects yet</h3>
            <p>Create a subject to start planning.</p>
        </article>
    <?php else: ?>
        <div class="subject-grid">
            <?php foreach ($subjectCards as $subject): ?>
                <article class="card subject-card">
                    <div class="subject-card-top">
                        <div>
                            <p class="eyebrow"><?= e($subject['difficulty_label']) ?> Subject</p>
                            <h3><?= e($subject['name']) ?></h3>
                        </div>
                        <span class="badge badge-neutral"><?= $subject['difficulty_value'] ?>/3</span>
                    </div>

                    <div class="subject-meta">
                        <span>Exam: <?= e(formatFriendlyDate($subject['exam_date'])) ?></span>
                        <span><?= $subject['days_left'] === 0 ? 'Past exam date' : $subject['days_left'] . ' day(s) left' ?></span>
                    </div>

                    <div class="metric-row">
                        <div>
                            <p class="mini-label">Priority</p>
                            <strong><?= e(number_format((float) $subject['priority_score'], 2)) ?></strong>
                        </div>
                        <div>
                            <p class="mini-label">Planned hours</p>
                            <strong><?= formatHours((float) $subject['progress']['planned_hours']) ?></strong>
                        </div>
                    </div>

                    <div class="progress-block">
                        <div class="progress-head">
                            <span>Progress</span>
                            <strong><?= $subject['progress']['progress_percentage'] ?>%</strong>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: <?= $subject['progress']['progress_percentage'] ?>%"></div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a class="button button-secondary" href="<?= e(url('/pages/edit_subject.php?id=' . $subject['id'])) ?>">Edit</a>
                        <form method="post" class="inline-delete-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete_subject">
                            <input type="hidden" name="subject_id" value="<?= $subject['id'] ?>">
                            <button class="button button-danger" type="submit" data-confirm-delete>Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div class="visually-hidden" data-reminder-payload="<?= jsonForAttribute($reminderPayload) ?>"></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
