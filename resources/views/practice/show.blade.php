<x-layouts.app title="Practice - CertPath 123">
  <div class="app-shell">
    <aside class="sidebar">
      <div class="brand">
        <h1>CertPath 123</h1>
        <p>{{ ucfirst($attempt->attempt_type) }} attempt</p>
      </div>
      <nav class="main-nav" aria-label="Practice navigation">
        <a href="{{ route('certifications.show', ['certificationSlug' => $attempt->certification->slug]) }}">Workspace</a>
        <a href="#questions">Questions</a>
        <a href="#results">Results</a>
      </nav>
    </aside>

    <main class="page">
      <header class="topbar">
        <div>
          <p class="eyebrow">{{ $attempt->certification->exam_code }} / {{ ucfirst($attempt->attempt_type) }}</p>
          <h2>{{ $attempt->status }}</h2>
          <p>
            Started {{ $attempt->started_at->format('M j, Y H:i') }}
            @if ($attempt->expires_at)
              - due {{ $attempt->expires_at->format('H:i') }}
            @endif
          </p>
        </div>
        <div class="profile-card">
          <strong>{{ $attempt->score_percent ?? 0 }}% score</strong>
          <span>{{ $attempt->total_questions }} questions</span>
        </div>
      </header>

      @if (session('status'))
        <div class="content flash-message" role="status">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="content form-error" role="alert">{{ $errors->first() }}</div>
      @endif

      <section id="questions" class="content">
        <form method="POST" action="{{ route('quiz-attempts.submit', ['quizAttempt' => $attempt->id]) }}" class="quiz-form">
          @csrf
          @foreach ($attempt->questions as $attemptQuestion)
            <article class="panel quiz-question">
              <p class="eyebrow">Question {{ $attemptQuestion->position }}</p>
              <h3>{{ $attemptQuestion->version->prompt_markdown }}</h3>
              <div class="option-list">
                @foreach ($attemptQuestion->version->options as $option)
                  <label class="option-row">
                    <input
                      name="answers[{{ $attemptQuestion->id }}]"
                      type="radio"
                      value="{{ $option->id }}"
                      @checked($attemptQuestion->answer?->selected_option_id === $option->id)
                      @disabled($attempt->status !== 'In_progress')
                    >
                    <span>{{ $option->option_key }}. {{ $option->body_markdown }}</span>
                  </label>
                @endforeach
              </div>
              @if ($attempt->status !== 'In_progress')
                <p class="muted">
                  Result:
                  <strong>{{ $attemptQuestion->is_correct ? 'Correct' : 'Not correct' }}</strong>
                </p>
                <p>{{ $attemptQuestion->version->explanation_markdown }}</p>
              @endif
            </article>
          @endforeach

          @if ($attempt->status === 'In_progress')
            <button type="submit" class="primary-action">Submit attempt</button>
          @endif
        </form>
      </section>

      @if ($attempt->status !== 'In_progress')
        <section id="results" class="content">
          <div class="section-heading">
            <h2>Domain scores</h2>
            <p>{{ $attempt->correct_count }} correct, {{ $attempt->incorrect_count }} incorrect, {{ $attempt->unanswered_count }} unanswered.</p>
          </div>
          <div class="cards-grid">
            @foreach ($attempt->domainScores as $domainScore)
              <article class="card">
                <h3>{{ $domainScore->domain?->name }}</h3>
                <p>{{ $domainScore->score_percent }}%</p>
                <p class="muted">{{ $domainScore->correct_count }} of {{ $domainScore->total_count }} correct</p>
              </article>
            @endforeach
          </div>
        </section>
      @endif
    </main>
  </div>
</x-layouts.app>
