<?php
$page='participants';
require_once __DIR__.'/db.php';
auth_require_module('participants');

$id = (int)($_GET['id'] ?? 0);
if(!$id){
  $_SESSION['flash'] = 'Invalid participant ID';
  redirect('participants.php');
}

$stmt = $pdo->prepare("
  SELECT p.*,
         w.title as workshop_title,
         w.date as workshop_date,
         w.location as workshop_location
  FROM participants p
  LEFT JOIN workshops w ON w.id = p.workshop_id
  WHERE p.id = ?
");
$stmt->execute([$id]);
$participant = $stmt->fetch();

if(!$participant){
  $_SESSION['flash'] = 'Participant not found';
  redirect('participants.php');
}

$pageTitle = 'View Participant';
require_once __DIR__.'/header.php';
?>

<div class="profile-container">
  <!-- Profile Header -->
  <div class="profile-header-card">
    <div class="profile-header-content">
      <div class="profile-photo-section">
        <?php if($participant['photo']): ?>
          <img src="uploads/<?= e($participant['photo']) ?>" alt="<?= e($participant['name']) ?>" class="profile-photo">
        <?php else: ?>
          <div class="profile-photo-placeholder">
            <?= e(strtoupper(substr($participant['name'], 0, 1))) ?>
          </div>
        <?php endif; ?>
        <div class="profile-status-badge <?= $participant['attended'] ? 'attended' : 'not-attended' ?>">
          <?= $participant['attended'] ? '✓ Attended' : 'Not Attended' ?>
        </div>
      </div>

      <div class="profile-info-section">
        <div class="profile-name-section">
          <h1 class="profile-name"><?= e($participant['name']) ?></h1>
          <p class="profile-designation"><?= e($participant['designation'] ?: 'No designation specified') ?></p>
        </div>

        <div class="profile-meta">
          <div class="meta-item">
            <i class='bx bx-user'></i>
            <span><?= e($participant['gender']) ?></span>
          </div>
          <?php if($participant['workshop_title']): ?>
          <div class="meta-item">
            <i class='bx bx-calendar-event'></i>
            <span><?= e($participant['workshop_title']) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="profile-actions">
      <a href="participant_form.php?id=<?= $participant['id'] ?>" class="btn">
        <i class='bx bx-edit'></i> Edit Participant
      </a>
      <a href="participants.php" class="btn sec">
        <i class='bx bx-arrow-back'></i> Back to List
      </a>
    </div>
  </div>

  <!-- Profile Details -->
  <div class="profile-details-grid">
    <!-- Contact Information Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-mail-send'></i>
        <h3>Contact Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <div class="detail-label">Email Address</div>
          <div class="detail-value">
            <?php if($participant['email']): ?>
              <a href="mailto:<?= e($participant['email']) ?>" class="detail-link">
                <?= e($participant['email']) ?>
              </a>
            <?php else: ?>
              <span class="detail-empty">Not provided</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Contact Number</div>
          <div class="detail-value">
            <?php if($participant['contact']): ?>
              <a href="tel:<?= e($participant['contact']) ?>" class="detail-link">
                <?= e($participant['contact']) ?>
              </a>
            <?php else: ?>
              <span class="detail-empty">Not provided</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Province</div>
          <div class="detail-value">
            <?= e($participant['province'] ?: 'Not specified') ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Workshop Information Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-calendar'></i>
        <h3>Workshop Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <div class="detail-label">Workshop Title</div>
          <div class="detail-value">
            <?= e($participant['workshop_title'] ?: 'Not assigned') ?>
          </div>
        </div>

        <?php if($participant['workshop_date']): ?>
        <div class="detail-row">
          <div class="detail-label">Workshop Date</div>
          <div class="detail-value">
            <i class='bx bx-calendar'></i>
            <?= date('F j, Y', strtotime($participant['workshop_date'])) ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if($participant['workshop_location']): ?>
        <div class="detail-row">
          <div class="detail-label">Location</div>
          <div class="detail-value">
            <i class='bx bx-map'></i>
            <?= e($participant['workshop_location']) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Additional Information Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-info-circle'></i>
        <h3>Additional Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <div class="detail-label">Gender</div>
          <div class="detail-value">
            <span class="gender-badge <?= strtolower($participant['gender']) ?>">
              <?= e($participant['gender']) ?>
            </span>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Attendance Status</div>
          <div class="detail-value">
            <span class="attendance-badge <?= $participant['attended'] ? 'present' : 'absent' ?>">
              <?= $participant['attended'] ? '✓ Present' : '✗ Absent' ?>
            </span>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Participant ID</div>
          <div class="detail-value">
            <span class="participant-id">#<?= str_pad($participant['id'], 4, '0', STR_PAD_LEFT) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.profile-container {
  width: 100%;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.profile-header-card {
  background: linear-gradient(135deg, #009EDB 0%, #005B8E 100%);
  border-radius: 12px;
  padding: 32px 24px;
  color: white;
  box-shadow: 0 4px 20px rgba(0, 158, 219, 0.3);
}

.profile-header-content {
  display: flex;
  gap: 24px;
  align-items: flex-start;
  margin-bottom: 24px;
}

.profile-photo-section {
  position: relative;
  flex-shrink: 0;
}

.profile-photo {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-photo-placeholder {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  font-weight: 700;
  border: 4px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
}

.profile-status-badge {
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 16px;
  border-radius: 16px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.profile-status-badge.attended {
  background: #27ae60;
  color: white;
}

.profile-status-badge.not-attended {
  background: #e74c3c;
  color: white;
}

.profile-info-section {
  flex: 1;
  padding-top: 8px;
}

.profile-name-section {
  margin-bottom: 16px;
}

.profile-name {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: white;
  line-height: 1.2;
}

.profile-designation {
  margin: 0;
  font-size: 16px;
  color: rgba(255, 255, 255, 0.8);
}

.profile-meta {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  backdrop-filter: blur(10px);
}

.meta-item i {
  font-size: 18px;
}

.profile-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.profile-details-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  width: 100%;
}

.detail-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.detail-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.detail-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: #f8fafb;
  border-bottom: 1px solid var(--border);
}

.detail-card-header i {
  font-size: 22px;
  color: var(--primary);
}

.detail-card-header h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: var(--text);
}

.detail-card-body {
  padding: 16px 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #f0f2f5;
}

.detail-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.detail-row:first-child {
  padding-top: 0;
}

.detail-label {
  font-size: 13px;
  color: var(--muted);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
}

.detail-value {
  font-size: 14px;
  color: var(--text);
  font-weight: 500;
  text-align: right;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.detail-link {
  color: var(--primary);
  text-decoration: none;
  transition: color 0.2s;
}

.detail-link:hover {
  color: var(--primary-dark);
  text-decoration: underline;
}

.detail-empty {
  color: var(--tert);
  font-style: italic;
}

.gender-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.gender-badge.male {
  background: #e3f2fd;
  color: #1976d2;
}

.gender-badge.female {
  background: #fce4ec;
  color: #c2185b;
}

.attendance-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.attendance-badge.present {
  background: #d4edda;
  color: #155724;
}

.attendance-badge.absent {
  background: #f8d7da;
  color: #721c24;
}

.participant-id {
  font-family: monospace;
  background: #f8fafb;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: var(--muted);
}

@media (max-width: 1024px) {
  .profile-details-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .profile-header-card {
    padding: 24px 20px;
  }

  .profile-header-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .profile-info-section {
    width: 100%;
  }

  .profile-meta {
    justify-content: center;
  }

  .profile-actions {
    flex-direction: column;
  }

  .profile-actions .btn {
    width: 100%;
    justify-content: center;
  }

  .profile-details-grid {
    grid-template-columns: 1fr;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .detail-value {
    text-align: left;
    justify-content: flex-start;
  }
}
</style>

<?php require __DIR__.'/footer.php'; ?>