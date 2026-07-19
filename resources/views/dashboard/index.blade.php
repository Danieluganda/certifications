<x-layouts.app title="CertPath 123">
  <div class="app-shell">
    <aside class="sidebar">
      <div class="brand">
        <h1>CertPath 123</h1>
        <p>Excelling at certifications made easy as 1-2-3.</p>
      </div>
      <nav class="main-nav" aria-label="Main navigation">
        <a href="#today">Today</a>
        <a href="#catalogue">Catalogue</a>
        <a href="#planner">Planner</a>
        <a href="#roadmap">Roadmap</a>
        <a href="#workspace">Workspace</a>
        <a href="#projects">Projects</a>
        <a href="#resources">Resources</a>
        <a href="#progress">Progress</a>
        <a href="{{ route('exports.learning-backup') }}">Backup</a>
      </nav>
    </aside>

    <main class="page">
      <header class="topbar">
        <div>
          <p class="eyebrow">Private learning system</p>
          <h2>Today</h2>
          <p>What should I do next?</p>
        </div>
        <div class="profile-card">
          <strong>{{ $user?->name ?? 'Personal learner' }}</strong>
          <span>{{ $user?->profile?->weekly_target_minutes ?? 0 }} weekly target minutes</span>
          <a class="link-button" href="{{ route('exports.learning-backup') }}">Download backup</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="link-button" type="submit">Sign out</button>
          </form>
        </div>
      </header>

      @if (session('status'))
        <div class="content flash-message" role="status">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="content form-error" role="alert">{{ $errors->first() }}</div>
      @endif

      <section id="today" class="content grid dashboard-grid">
        <article class="panel hero-panel">
          <span class="badge paid">Paid professional</span>
          <h2>{{ $primary?->exam_code }}: {{ $primary?->name }}</h2>
          <p>{{ $primary?->provider?->name }} certification workspace is active. Keep the paid track focused while using free credentials for supporting momentum.</p>
          <div class="metric-row">
            <div>
              <strong>{{ $primary?->readiness_percent ?? 0 }}%</strong>
              <span>Readiness</span>
            </div>
            <div>
              <strong>{{ $primary?->progress_percent ?? 0 }}%</strong>
              <span>Progress</span>
            </div>
            <div>
              <strong>{{ $primary?->lessons?->count() ?? 0 }}</strong>
              <span>Lessons</span>
            </div>
          </div>
        </article>

        <aside class="panel">
          <h3>Active free lane</h3>
          @forelse ($activeFreeCredentials as $credential)
            <div class="mini-card">
              <span class="badge free">Free credential</span>
              <strong>{{ $credential->name }}</strong>
              <p>{{ $credential->progress_percent }}% complete</p>
            </div>
          @empty
            <p class="muted">No active free credential yet.</p>
          @endforelse
        </aside>
      </section>

      <section id="catalogue" class="content">
        <div class="section-heading">
          <h2>Add certification</h2>
          <p>Create a paid certification or a supporting free credential.</p>
        </div>
        <form method="POST" action="{{ route('certifications.store') }}" class="catalogue-form panel">
          @csrf
          <label for="provider-name">
            Provider
            <input id="provider-name" name="provider_name" type="text" value="{{ old('provider_name') }}" required>
          </label>
          <label for="certification-name">
            Certification
            <input id="certification-name" name="name" type="text" value="{{ old('name') }}" required>
          </label>
          <label for="exam-code">
            Exam code
            <input id="exam-code" name="exam_code" type="text" value="{{ old('exam_code') }}">
          </label>
          <label for="track-type">
            Track
            <select id="track-type" name="track_type" required>
              <option value="paid_professional" @selected(old('track_type') === 'paid_professional')>Paid professional</option>
              <option value="free_credential" @selected(old('track_type') === 'free_credential')>Free credential</option>
            </select>
          </label>
          <label for="target-completion-date">
            Target date
            <input id="target-completion-date" name="target_completion_date" type="date" value="{{ old('target_completion_date') }}">
          </label>
          <label for="weekly-minutes">
            Weekly minutes
            <input id="weekly-minutes" name="weekly_minutes" type="number" min="0" max="10080" value="{{ old('weekly_minutes', 120) }}">
          </label>
          <button type="submit" class="primary-action">Add</button>
        </form>
      </section>

      <section id="planner" class="content planner-grid">
        <div>
          <div class="section-heading">
            <h2>Study planner</h2>
            <p>Schedule focused sessions without overloading the week.</p>
          </div>
          <form method="POST" action="{{ route('study-sessions.store') }}" class="panel study-form">
            @csrf
            <label for="session-certification">
              Certification
              <select id="session-certification" name="certification_id" required>
                @foreach ($certifications as $certification)
                  <option value="{{ $certification->id }}">{{ $certification->exam_code }}: {{ $certification->name }}</option>
                @endforeach
              </select>
            </label>
            <label for="session-activity">
              Activity
              <select id="session-activity" name="activity_type" required>
                <option value="Lesson">Lesson</option>
                <option value="quiz">Quiz</option>
                <option value="review">Review</option>
                <option value="lab">Lab</option>
                <option value="project">Project</option>
              </select>
            </label>
            <label for="session-scheduled-for">
              Scheduled for
              <input id="session-scheduled-for" name="scheduled_for" type="datetime-local" required>
            </label>
            <label for="session-minutes">
              Minutes
              <input id="session-minutes" name="planned_minutes" type="number" min="5" max="480" value="45" required>
            </label>
            <label for="session-notes">
              Notes
              <textarea id="session-notes" name="notes" rows="3"></textarea>
            </label>
            <button type="submit" class="primary-action">Schedule</button>
          </form>
        </div>

        <aside class="panel">
          <h3>Upcoming sessions</h3>
          <div class="session-list">
            @forelse ($studySessions as $session)
              <article class="session-item">
                <strong>{{ $session->activity_type }} - {{ $session->certification?->exam_code }}</strong>
                <span>{{ $session->scheduled_for->format('M j, Y H:i') }} / {{ $session->planned_minutes }} min</span>
                @if ($session->notes)
                  <p>{{ $session->notes }}</p>
                @endif
                <form method="POST" action="{{ route('study-sessions.complete', ['studySession' => $session->id]) }}">
                  @csrf
                  <button type="submit" class="secondary-action">Complete</button>
                </form>
              </article>
            @empty
              <p class="muted">No sessions scheduled yet.</p>
            @endforelse
          </div>
        </aside>
      </section>

      <section id="roadmap" class="content">
        <div class="section-heading">
          <h2>Roadmap</h2>
          <p>Paid professional lane and free continuous lane.</p>
        </div>
        <div class="roadmap-lanes">
          <div class="lane">
            <h3>Paid professional lane</h3>
            @foreach ($certifications->where('track_type.value', 'paid_professional') as $certification)
              <article class="card lane-item paid">
                <span class="badge paid">Paid professional</span>
                <h3><a href="{{ route('certifications.show', ['certificationSlug' => $certification->slug]) }}">{{ $certification->exam_code }}: {{ $certification->name }}</a></h3>
                <p class="muted">{{ $certification->status }} - target {{ optional($certification->target_completion_date)->format('M j, Y') ?? 'not set' }}</p>
                @if (! $certification->is_primary)
                  <form method="POST" action="{{ route('certifications.primary.store', ['certificationSlug' => $certification->slug]) }}">
                    @csrf
                    <button type="submit" class="secondary-action">Make primary</button>
                  </form>
                @else
                  <p class="muted"><strong>Primary certification</strong></p>
                @endif
              </article>
            @endforeach
          </div>
          <div class="lane">
            <h3>Free continuous lane</h3>
            @foreach ($certifications->where('track_type.value', 'free_credential') as $certification)
              <article class="card lane-item free">
                <span class="badge free">Free credential</span>
                <h3><a href="{{ route('certifications.show', ['certificationSlug' => $certification->slug]) }}">{{ $certification->exam_code }}: {{ $certification->name }}</a></h3>
                <p class="muted">{{ $certification->status }} - target {{ optional($certification->target_completion_date)->format('M j, Y') ?? 'not set' }}</p>
                @if ($certification->status !== 'Active')
                  <form method="POST" action="{{ route('certifications.free-activation.store', ['certificationSlug' => $certification->slug]) }}">
                    @csrf
                    <button type="submit" class="secondary-action">Activate</button>
                  </form>
                @else
                  <p class="muted"><strong>Active credential</strong></p>
                @endif
              </article>
            @endforeach
          </div>
        </div>
      </section>

      <section id="workspace" class="content">
        <div class="section-heading">
          <h2>Certification workspace</h2>
          <p>Overview, curriculum, lessons, projects, resources, and progress.</p>
        </div>
        <div class="workspace-grid">
          <aside class="panel">
            <h3>Curriculum</h3>
            @foreach ($primary?->domains ?? [] as $domain)
              <div class="progress-block">
                <div class="progress-line">
                  <span>{{ $domain->name }}</span>
                  <strong>{{ $domain->mastery_percent }}%</strong>
                </div>
                <div class="progress-track"><span style="width: {{ $domain->mastery_percent }}%"></span></div>
              </div>
            @endforeach
          </aside>

          <article class="panel lesson-panel">
            @php($lesson = $primary?->lessons?->first())
            @if ($lesson)
              <p class="eyebrow">{{ $lesson->domain?->name }} / {{ $lesson->topic_name }}</p>
              <h2>{{ $lesson->title }}</h2>
              <p>{{ $lesson->summary }}</p>
              <h3>Learn</h3>
              @foreach (preg_split("/\n\n+/", $lesson->body_markdown) as $paragraph)
                <p>{{ $paragraph }}</p>
              @endforeach
              <h3>Example</h3>
              <pre>{{ $lesson->example_markdown }}</pre>
              <h3>Proof task</h3>
              <p>{{ $lesson->proof_task }}</p>
            @else
              <p>No lesson has been added yet.</p>
            @endif
          </article>
        </div>
      </section>

      <section id="projects" class="content">
        <div class="section-heading">
          <h2>Projects</h2>
          <p>Every paid certification needs portfolio evidence.</p>
        </div>
        <div class="cards-grid">
          @foreach ($projects as $project)
            <article class="card">
              <span class="badge {{ $project->certification->track_type->value === 'paid_professional' ? 'paid' : 'free' }}">{{ $project->certification->exam_code }}</span>
              <h3>{{ $project->title }}</h3>
              <p>{{ $project->business_problem }}</p>
              <p class="muted"><strong>Next:</strong> {{ $project->next_milestone }}</p>
            </article>
          @endforeach
        </div>
      </section>

      <section id="resources" class="content">
        <div class="section-heading">
          <h2>Resources</h2>
          <p>Official sources are linked, not copied.</p>
        </div>
        <div class="resource-list">
          @foreach ($resources as $resource)
            <article class="resource-row">
              <div>
                <strong>
                  @if ($resource->url)
                    <a href="{{ $resource->url }}" target="_blank" rel="noreferrer">{{ $resource->title }}</a>
                  @else
                    {{ $resource->title }}
                  @endif
                </strong>
                <p class="muted">{{ $resource->provider_name }} - {{ $resource->resource_type }} - {{ $resource->copyright_status }}</p>
              </div>
              <span class="badge free">{{ $resource->status }}</span>
            </article>
          @endforeach
        </div>
      </section>
    </main>
  </div>
</x-layouts.app>
