<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['personal_info_id'])) {
  header('Location: personal_info.php');
  exit;
}
include_once __DIR__ . '/includes/db.php';
?>
<?php
$pageTitle = 'Pertanyaan Minat Kerja - RIASEC';
$query = "SELECT statement_id, statement_content, statement_category FROM statements ORDER BY statement_id ASC";
$statementSelectQuery = mysqli_query($connection, $query);
$questions = array();
if ($statementSelectQuery) {
    while ($row = mysqli_fetch_assoc($statementSelectQuery)) {
        $questions[] = $row;
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card app-form-card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
      <div>
        <p class="mb-1"><a href="personal_info.php" class="text-decoration-none">&larr; Kembali ke data peserta</a></p>
        <h1 class="h3 fw-bold text-success mb-1">Activity Questions</h1>
        <p class="muted mb-0">Nilai seberapa kamu ingin melakukan aktivitas ini jika menjadi bagian dari pekerjaanmu.</p>
      </div>
      <div class="badge text-bg-light border">Shortcut keyboard: 1 - 5</div>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'REQ') { ?>
      <div class="alert alert-danger" role="alert">
        Semua pertanyaan dan persetujuan penyimpanan data wajib diisi sebelum melihat hasil.
      </div>
    <?php } ?>

    <?php if (empty($questions)) { ?>
      <div class="alert alert-warning mb-0">Belum ada data pernyataan pada tabel <code>statements</code>.</div>
    <?php } else { ?>
      <form action="result.php" method="post" id="riasecForm">
        <div class="question-shell">
          <div class="progress-top">
            <span class="counter"><span id="currentQuestion">1</span> dari <span id="totalQuestion"><?php echo count($questions); ?></span></span>
            <span class="badge text-bg-success">Profiler Minat Kerja</span>
          </div>

          <div class="progress mb-3" role="progressbar" aria-label="Progress pertanyaan" aria-valuemin="0" aria-valuemax="<?php echo count($questions); ?>">
            <div id="questionProgress" class="progress-bar" style="width: 0%;"></div>
          </div>

          <div id="questionContainer">
            <?php foreach ($questions as $index => $q) { ?>
              <?php $name = $q['statement_category'] . intval($q['statement_id']); ?>
              <input type="hidden" name="<?php echo htmlspecialchars($name); ?>" id="input-<?php echo htmlspecialchars($name); ?>" value="">
              <section
                class="question-card riasec-question"
                data-question-index="<?php echo $index; ?>"
                data-input-id="input-<?php echo htmlspecialchars($name); ?>"
                style="<?php echo $index === 0 ? '' : 'display:none;'; ?>"
              >
                <h2 class="question-text"><?php echo htmlspecialchars($q['statement_content']); ?></h2>
                <div class="answer-scale">
                  <button type="button" class="answer-btn" data-value="1" aria-label="Sangat tidak suka">
                    <span class="face">😫</span>
                    <span class="label">Sangat Tidak Suka</span>
                  </button>
                  <button type="button" class="answer-btn" data-value="2" aria-label="Tidak suka">
                    <span class="face">🙁</span>
                    <span class="label">Tidak Suka</span>
                  </button>
                  <button type="button" class="answer-btn" data-value="3" aria-label="Ragu-ragu">
                    <span class="face">😐</span>
                    <span class="label">Ragu-ragu</span>
                  </button>
                  <button type="button" class="answer-btn" data-value="4" aria-label="Suka">
                    <span class="face">🙂</span>
                    <span class="label">Suka</span>
                  </button>
                  <button type="button" class="answer-btn" data-value="5" aria-label="Sangat suka">
                    <span class="face">😁</span>
                    <span class="label">Sangat Suka</span>
                  </button>
                </div>
              </section>
            <?php } ?>
          </div>
        </div>

        <div class="glass-card app-form-card mt-3">
          <div class="mb-3">
            <div class="fw-bold mb-2">Peta pertanyaan (klik nomor untuk lompat):</div>
            <div id="questionMap" class="question-map"></div>
          </div>

          <div id="unansweredSection" class="alert alert-warning d-none" role="alert">
            <div class="fw-bold mb-1">Masih ada pertanyaan yang belum dijawab.</div>
            <div class="small mb-2">Nomor yang belum dijawab: <span id="unansweredList">-</span></div>
            <button type="button" class="btn btn-sm btn-outline-dark" id="jumpFirstUnansweredBtn">
              Lompat ke nomor pertama yang kosong
            </button>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="can_save_data" value="true" id="saveDataYes" required>
            <label class="form-check-label" for="saveDataYes">
              Saya setuju jawaban saya disimpan untuk keperluan konseling dan pengembangan asesmen.
            </label>
          </div>

          <div class="question-nav">
            <button type="button" class="btn btn-outline-secondary" id="prevBtn">Sebelumnya</button>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-success" id="nextBtn">Berikutnya</button>
              <button type="submit" name="submit" class="btn btn-primary-soft" id="submitBtn" disabled>Lihat Hasil</button>
            </div>
          </div>
        </div>
      </form>

      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const questions = Array.from(document.querySelectorAll('.riasec-question'));
          const total = questions.length;
          const currentQuestionEl = document.getElementById('currentQuestion');
          const progressEl = document.getElementById('questionProgress');
          const prevBtn = document.getElementById('prevBtn');
          const nextBtn = document.getElementById('nextBtn');
          const submitBtn = document.getElementById('submitBtn');
          const saveDataCheckbox = document.getElementById('saveDataYes');
          const form = document.getElementById('riasecForm');
          const unansweredSection = document.getElementById('unansweredSection');
          const unansweredListEl = document.getElementById('unansweredList');
          const jumpFirstUnansweredBtn = document.getElementById('jumpFirstUnansweredBtn');
          const questionMap = document.getElementById('questionMap');

          let currentIndex = 0;

          function getHiddenInput(questionEl) {
            return document.getElementById(questionEl.dataset.inputId);
          }

          function setActiveButtonState(questionEl, value) {
            const buttons = questionEl.querySelectorAll('.answer-btn');
            buttons.forEach((btn) => {
              const active = btn.dataset.value === String(value);
              btn.classList.toggle('active', active);
            });
          }

          function isAllAnswered() {
            return questions.every((q) => {
              const input = getHiddenInput(q);
              const val = Number(input.value);
              return val >= 1 && val <= 5;
            });
          }

          function getUnansweredQuestionNumbers() {
            const missing = [];
            questions.forEach((q, idx) => {
              const input = getHiddenInput(q);
              const val = Number(input.value);
              if (!(val >= 1 && val <= 5)) {
                missing.push(idx + 1);
              }
            });
            return missing;
          }

          function refreshUnansweredSection() {
            const missing = getUnansweredQuestionNumbers();
            if (missing.length === 0) {
              unansweredSection.classList.add('d-none');
              unansweredListEl.textContent = '-';
              return;
            }

            unansweredSection.classList.remove('d-none');
            unansweredListEl.textContent = missing.join(', ');
          }

          function refreshQuestionMap() {
            const missingSet = new Set(getUnansweredQuestionNumbers());
            const buttons = questionMap.querySelectorAll('.question-map-btn');
            buttons.forEach((btn, idx) => {
              const qNum = idx + 1;
              btn.classList.remove('current', 'answered', 'unanswered');
              if (idx === currentIndex) {
                btn.classList.add('current');
              }
              if (missingSet.has(qNum)) {
                btn.classList.add('unanswered');
              } else {
                btn.classList.add('answered');
              }
            });
          }

          function refreshActionButtons() {
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex >= total - 1;
            submitBtn.disabled = !(isAllAnswered() && saveDataCheckbox.checked);
            refreshUnansweredSection();
            refreshQuestionMap();
          }

          function renderQuestion(index) {
            questions.forEach((q, idx) => {
              q.style.display = idx === index ? '' : 'none';
            });
            currentQuestionEl.textContent = String(index + 1);
            const pct = Math.round(((index + 1) / total) * 100);
            progressEl.style.width = pct + '%';
            refreshActionButtons();
          }

          questions.forEach((questionEl) => {
            const input = getHiddenInput(questionEl);
            const buttons = questionEl.querySelectorAll('.answer-btn');

            buttons.forEach((button) => {
              button.addEventListener('click', function () {
                input.value = this.dataset.value;
                setActiveButtonState(questionEl, this.dataset.value);
                refreshActionButtons();
              });
            });
          });

          questions.forEach((_, idx) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'question-map-btn unanswered';
            button.textContent = String(idx + 1);
            button.setAttribute('aria-label', 'Lompat ke pertanyaan ' + String(idx + 1));
            button.addEventListener('click', function () {
              currentIndex = idx;
              renderQuestion(currentIndex);
            });
            questionMap.appendChild(button);
          });

          prevBtn.addEventListener('click', function () {
            if (currentIndex > 0) {
              currentIndex -= 1;
              renderQuestion(currentIndex);
            }
          });

          nextBtn.addEventListener('click', function () {
            if (currentIndex < total - 1) {
              currentIndex += 1;
              renderQuestion(currentIndex);
            }
          });

          saveDataCheckbox.addEventListener('change', refreshActionButtons);
          jumpFirstUnansweredBtn.addEventListener('click', function () {
            const missing = getUnansweredQuestionNumbers();
            if (missing.length > 0) {
              currentIndex = missing[0] - 1;
              renderQuestion(currentIndex);
            }
          });

          document.addEventListener('keydown', function (event) {
            if (!/^[1-5]$/.test(event.key)) {
              return;
            }
            const activeQuestion = questions[currentIndex];
            if (!activeQuestion) {
              return;
            }
            const button = activeQuestion.querySelector('.answer-btn[data-value="' + event.key + '"]');
            if (button) {
              button.click();
            }
          });

          form.addEventListener('submit', function (event) {
            if (!(isAllAnswered() && saveDataCheckbox.checked)) {
              event.preventDefault();
              const missing = getUnansweredQuestionNumbers();
              if (missing.length > 0) {
                currentIndex = missing[0] - 1;
                renderQuestion(currentIndex);
              }
            }
          });

          renderQuestion(currentIndex);
        });
      </script>
    <?php } ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>