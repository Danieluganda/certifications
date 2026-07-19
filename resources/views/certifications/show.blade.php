<x-layouts.app title="{{ $certification->exam_code }} - CertPath 123">
  <div class="app-shell">
    <aside class="sidebar">
      <div class="brand">
        <h1>CertPath 123</h1>
        <p>{{ $certification->exam_code }} workspace</p>
      </div>
      <nav class="main-nav" aria-label="Workspace navigation">
        <a href="{{ route('dashboard') }}">Today</a>
        <a href="#overview">Overview</a>
        <a href="#curriculum">Curriculum</a>
        <a href="#lesson">Lesson</a>
        <a href="#projects">Projects</a>
        <a href="#resources">Resources</a>
      </nav>
    </aside>

    <main class="page">
      <header class="topbar">
        <div>
          <p class="eyebrow">{{ $certification->provider?->name }} / {{ $certification->track_type->label() }}</p>
          <h2>{{ $certification->exam_code }}: {{ $certification->name }}</h2>
          <p>{{ $certification->status }} - target {{ optional($certification->target_completion_date)->format('M j, Y') ?? 'not set' }}</p>
        </div>
        <div class="profile-card">
          <strong>{{ $certification->readiness_percent }}% readiness</strong>
          <span>{{ $certification->progress_percent }}% progress</span>
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

      <section id="overview" class="content grid dashboard-grid">
        <article class="panel hero-panel">
          <span class="badge {{ $certification->track_type->value === 'paid_professional' ? 'paid' : 'free' }}">{{ $certification->track_type->label() }}</span>
          <h2>Study workspace</h2>
          <p>This workspace connects objectives, lessons, practice, projects, resources, and progress for this certification.</p>
          <div class="metric-row">
            <div>
              <strong>{{ $certification->domains->count() }}</strong>
              <span>Domains</span>
            </div>
            <div>
              <strong>{{ $certification->lessons->count() }}</strong>
              <span>Lessons</span>
            </div>
            <div>
              <strong>{{ $certification->projects->count() }}</strong>
              <span>Projects</span>
            </div>
          </div>
        </article>

        <aside id="curriculum" class="panel">
          <h3>Domain mastery</h3>
          @foreach ($certification->domains as $domain)
            <div class="progress-block">
              <div class="progress-line">
                <span>{{ $domain->name }} @if($domain->weight_percent)({{ (int) $domain->weight_percent }}%)@endif</span>
                <strong>{{ $domain->mastery_percent }}%</strong>
              </div>
              <div class="progress-track"><span style="width: {{ $domain->mastery_percent }}%"></span></div>
              @if ($domain->topics->isNotEmpty())
                <ul class="topic-list">
                  @foreach ($domain->topics as $topic)
                    <li>
                      <strong>{{ $topic->name }}</strong>
                      @if ($topic->prerequisites)
                        <span>{{ $topic->prerequisites }}</span>
                      @endif
                    </li>
                  @endforeach
                </ul>
              @endif
            </div>
          @endforeach
        </aside>
      </section>

      <section class="content curriculum-tools">
        <form method="POST" action="{{ route('domains.store', ['certificationSlug' => $certification->slug]) }}" class="panel study-form">
          @csrf
          <h3>Add domain</h3>
          <label for="domain-name">
            Name
            <input id="domain-name" name="name" type="text" value="{{ old('name') }}" required>
          </label>
          <label for="domain-weight">
            Weight percent
            <input id="domain-weight" name="weight_percent" type="number" min="0" max="100" step="0.01" value="{{ old('weight_percent') }}">
          </label>
          <button type="submit" class="primary-action">Add domain</button>
        </form>

        <form method="POST" action="{{ route('topics.store', ['certificationSlug' => $certification->slug]) }}" class="panel study-form">
          @csrf
          <h3>Add topic</h3>
          <label for="topic-domain">
            Domain
            <select id="topic-domain" name="domain_id" required>
              @foreach ($certification->domains as $domain)
                <option value="{{ $domain->id }}">{{ $domain->name }}</option>
              @endforeach
            </select>
          </label>
          <label for="topic-name">
            Topic
            <input id="topic-name" name="name" type="text" value="{{ old('name') }}" required>
          </label>
          <label for="topic-prerequisites">
            Prerequisites
            <textarea id="topic-prerequisites" name="prerequisites" rows="3">{{ old('prerequisites') }}</textarea>
          </label>
          <button type="submit" class="primary-action">Add topic</button>
        </form>
      </section>

      <section id="lesson" class="content workspace-grid">
        <aside class="panel">
          <h3>Lessons</h3>
          <div class="lesson-list">
            @foreach ($certification->lessons as $lesson)
              <a class="lesson-link {{ $selectedLesson?->id === $lesson->id ? 'active' : '' }}" href="{{ route('certifications.show', ['certificationSlug' => $certification->slug, 'lesson' => $lesson->external_id]) }}#lesson">
                <strong>{{ $lesson->title }}</strong>
                <span>{{ $lesson->domain?->name }} - {{ $lesson->estimated_minutes }} min</span>
              </a>
            @endforeach
          </div>
        </aside>

        <article class="panel lesson-panel">
          @if ($selectedLesson)
            <p class="eyebrow">{{ $selectedLesson->domain?->name }} / {{ $selectedLesson->topic_name }}</p>
            <h2>{{ $selectedLesson->title }}</h2>
            <p>{{ $selectedLesson->summary }}</p>

            <div class="study-tabs">
              <section>
                <h3>Learn</h3>
                @foreach (preg_split("/\n\n+/", $selectedLesson->body_markdown) as $paragraph)
                  <p>{{ $paragraph }}</p>
                @endforeach
              </section>

              <section>
                <h3>Example</h3>
                <pre>{{ $selectedLesson->example_markdown }}</pre>
              </section>

              <section>
                <h3>Try it</h3>
                <pre>{{ $selectedLesson->exercise_markdown }}</pre>
              </section>

              <section>
                <h3>Quick quiz</h3>
                @php($quiz = $selectedLesson->quiz_payload ?? [])
                <p>{{ $quiz['prompt'] ?? 'No quiz yet.' }}</p>
                @if (! empty($quiz['options']))
                  <ol class="quiz-list">
                    @foreach ($quiz['options'] as $option)
                      <li>{{ $option }}</li>
                    @endforeach
                  </ol>
                  <p class="muted"><strong>Explanation:</strong> {{ $quiz['explanation'] ?? '' }}</p>
                @endif
              </section>

              <section>
                <h3>Reference</h3>
                <ul>
                  @foreach (($selectedLesson->reference_payload ?? []) as $reference)
                    <li>{{ $reference }}</li>
                  @endforeach
                </ul>
              </section>

              <section class="callout">
                <h3>Proof task</h3>
                <p>{{ $selectedLesson->proof_task }}</p>
              </section>

              <section>
                <h3>{{ $completion ? 'Completion saved' : 'Mark complete' }}</h3>
                @if ($completion)
                  <p class="muted">
                    Completed {{ $completion->completed_at->diffForHumans() }}
                    @if ($completion->confidence)
                      with confidence {{ $completion->confidence }}/5
                    @endif
                  </p>
                @endif
                <form method="POST" action="{{ route('lessons.completions.store', ['certificationSlug' => $certification->slug, 'lesson' => $selectedLesson->id]) }}" class="study-form">
                  @csrf
                  <label for="confidence">
                    Confidence
                    <select id="confidence" name="confidence">
                      <option value="">Not set</option>
                      @for ($level = 1; $level <= 5; $level++)
                        <option value="{{ $level }}" @selected((int) old('confidence', $completion?->confidence) === $level)>{{ $level }}</option>
                      @endfor
                    </select>
                  </label>
                  <label for="completion-notes">
                    Completion notes
                    <textarea id="completion-notes" name="notes" rows="4">{{ old('notes', $completion?->notes) }}</textarea>
                  </label>
                  <button type="submit" class="primary-action">Save completion</button>
                </form>
              </section>

              <section id="notes">
                <h3>Lesson notes</h3>
                <form method="POST" action="{{ route('lessons.notes.store', ['certificationSlug' => $certification->slug, 'lesson' => $selectedLesson->id]) }}" class="study-form">
                  @csrf
                  <label for="note-title">
                    Title
                    <input id="note-title" name="title" type="text" value="{{ old('title', 'Lesson summary') }}" required>
                  </label>
                  <label for="note-body">
                    Markdown note
                    <textarea id="note-body" name="body_markdown" rows="5" required>{{ old('body_markdown') }}</textarea>
                  </label>
                  <label class="checkbox-row" for="note-favourite">
                    <input id="note-favourite" name="is_favourite" type="checkbox" value="1" @checked(old('is_favourite'))>
                    Favourite
                  </label>
                  <button type="submit" class="primary-action">Save note</button>
                </form>

                <div class="note-list">
                  @forelse ($notes as $note)
                    <article class="note-item">
                      <strong>{{ $note->title }}</strong>
                      <p>{{ $note->body_markdown }}</p>
                      <span class="muted">
                        {{ $note->created_at->diffForHumans() }}
                        @if ($note->is_favourite)
                          - favourite
                        @endif
                      </span>
                    </article>
                  @empty
                    <p class="muted">No notes for this lesson yet.</p>
                  @endforelse
                </div>
              </section>
            </div>
          @else
            <p>No lesson exists for this certification yet.</p>
          @endif
        </article>
      </section>

      <section id="projects" class="content">
        <div class="section-heading">
          <h2>Projects</h2>
          <p>Portfolio evidence connected to this certification.</p>
        </div>
        <div class="cards-grid">
          @foreach ($certification->projects as $project)
            <article class="card">
              <span class="badge {{ $certification->track_type->value === 'paid_professional' ? 'paid' : 'free' }}">{{ $project->status }}</span>
              <h3>{{ $project->title }}</h3>
              <p>{{ $project->business_problem }}</p>
              @if ($project->deliverables)
                <ul>
                  @foreach (array_slice($project->deliverables, 0, 6) as $deliverable)
                    <li>{{ $deliverable }}</li>
                  @endforeach
                </ul>
              @endif
              <p class="muted"><strong>Next:</strong> {{ $project->next_milestone }}</p>
            </article>
          @endforeach
        </div>
      </section>

      <section id="resources" class="content">
        <div class="section-heading">
          <h2>Resources</h2>
          <p>Official sources are linked, not copied into the app.</p>
        </div>
        <form method="POST" action="{{ route('resources.store', ['certificationSlug' => $certification->slug]) }}" class="panel resource-form">
          @csrf
          <label for="resource-title">
            Title
            <input id="resource-title" name="title" type="text" value="{{ old('title') }}" required>
          </label>
          <label for="resource-provider">
            Provider
            <input id="resource-provider" name="provider_name" type="text" value="{{ old('provider_name', $certification->provider?->name) }}" required>
          </label>
          <label for="resource-type">
            Type
            <select id="resource-type" name="resource_type" required>
              @foreach (['Official documentation', 'Official learning path', 'Video', 'Article', 'Book', 'PDF', 'Practice lab', 'Practice test', 'Community discussion', 'Personal note'] as $type)
                <option value="{{ $type }}" @selected(old('resource_type') === $type)>{{ $type }}</option>
              @endforeach
            </select>
          </label>
          <label for="resource-domain">
            Domain
            <select id="resource-domain" name="domain_id">
              <option value="">Any domain</option>
              @foreach ($certification->domains as $domain)
                <option value="{{ $domain->id }}" @selected((int) old('domain_id') === $domain->id)>{{ $domain->name }}</option>
              @endforeach
            </select>
          </label>
          <label for="resource-topic">
            Topic
            <select id="resource-topic" name="topic_id">
              <option value="">Any topic</option>
              @foreach ($certification->topics as $topic)
                <option value="{{ $topic->id }}" @selected((int) old('topic_id') === $topic->id)>{{ $topic->domain?->name }} / {{ $topic->name }}</option>
              @endforeach
            </select>
          </label>
          <label for="resource-url">
            URL
            <input id="resource-url" name="url" type="url" value="{{ old('url') }}">
          </label>
          <label for="resource-file">
            File path
            <input id="resource-file" name="file_path" type="text" value="{{ old('file_path') }}">
          </label>
          <label for="resource-trust">
            Trust level
            <select id="resource-trust" name="trust_level" required>
              @foreach (['Official', 'verified', 'community', 'personal'] as $trustLevel)
                <option value="{{ $trustLevel }}" @selected(old('trust_level', 'Official') === $trustLevel)>{{ $trustLevel }}</option>
              @endforeach
            </select>
          </label>
          <label for="resource-copyright">
            Copyright status
            <input id="resource-copyright" name="copyright_status" type="text" value="{{ old('copyright_status', 'linked_not_copied') }}" required>
          </label>
          <label for="resource-status">
            Status
            <select id="resource-status" name="status" required>
              @foreach (['Not started', 'in progress', 'completed'] as $status)
                <option value="{{ $status }}" @selected(old('status', 'Not started') === $status)>{{ $status }}</option>
              @endforeach
            </select>
          </label>
          <label for="resource-rating">
            Rating
            <select id="resource-rating" name="rating">
              <option value="">Not rated</option>
              @for ($rating = 1; $rating <= 5; $rating++)
                <option value="{{ $rating }}" @selected((int) old('rating') === $rating)>{{ $rating }}</option>
              @endfor
            </select>
          </label>
          <label for="resource-note" class="wide-field">
            Copyright note
            <textarea id="resource-note" name="copyright_note" rows="3">{{ old('copyright_note') }}</textarea>
          </label>
          <button type="submit" class="primary-action">Add resource</button>
        </form>
        <div class="resource-list">
          @foreach ($certification->resources as $resource)
            <article class="resource-row">
              <div>
                <strong>
                  @if ($resource->url)
                    <a href="{{ $resource->url }}" target="_blank" rel="noreferrer">{{ $resource->title }}</a>
                  @else
                    {{ $resource->title }}
                  @endif
                </strong>
                <p class="muted">
                  {{ $resource->provider_name }} - {{ $resource->resource_type }} - {{ $resource->trust_level }} - {{ $resource->copyright_status }}
                  @if ($resource->rating)
                    - {{ $resource->rating }}/5
                  @endif
                </p>
                @if ($resource->domain || $resource->topic)
                  <p class="muted">
                    {{ $resource->domain?->name }}
                    @if ($resource->topic)
                      / {{ $resource->topic->name }}
                    @endif
                  </p>
                @endif
              </div>
              <span class="badge free">{{ $resource->status }}</span>
            </article>
          @endforeach
        </div>
      </section>
    </main>
  </div>
</x-layouts.app>
