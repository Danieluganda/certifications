<x-layouts.app title="CertPath 123">
  <div class="app-shell">
    <aside class="sidebar">
      <div class="brand">
        <h1>CertPath 123</h1>
        <p>Excelling at certifications made easy as 1-2-3.</p>
      </div>
      <nav class="main-nav" aria-label="Main navigation">
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'learn']) }}">Learn</a>
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'catalogue']) }}">Certifications</a>
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'planner']) }}">Study Planner</a>
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'projects']) }}">Projects</a>
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'specialisations']) }}">Specialisations</a>
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'roadmap']) }}">Roadmap</a>
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'today']) }}">Today</a>
        <a href="{{ route('dashboard.page', ['dashboardPage' => 'workspace']) }}">Workspace</a>
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

      @if ($dashboardPage === 'learn')
      <section id="learn" class="content">
        <article class="panel hero-panel portal-hero">
          <p class="eyebrow">Welcome back, {{ $user?->name ?? 'Daniel' }}</p>
          <h2>What do you want to master today?</h2>
          <p>Search your certifications, lessons, topics, practice attempts, projects, and trusted resources from one learning portal.</p>
          <form class="portal-search" role="search" method="GET" action="{{ route('dashboard.page', ['dashboardPage' => 'learn']) }}">
            <label class="wide-field" for="portal-search">
              <span class="sr-only">Search CertPath</span>
              <input id="portal-search" name="search" type="search" placeholder="Search certifications, lessons, questions, projects..." value="{{ request('search') }}">
            </label>
            <button class="primary-action" type="submit">Search</button>
          </form>
        </article>
      </section>
      @endif

      @if ($dashboardPage === 'today')
      <section id="today" class="content grid dashboard-grid">
        <article class="panel hero-panel continue-card">
          <span class="badge paid">Paid professional</span>
          <h2>Continue {{ $primary?->exam_code }}: {{ $primary?->name }}</h2>
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
          @if ($primary)
            <p><a class="primary-action" href="{{ route('certifications.show', ['certificationSlug' => $primary->slug, 'workspacePage' => 'lesson']) }}">Continue learning</a></p>
          @endif
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
      @endif

      @if ($dashboardPage === 'learn')
      <section class="content">
        <div class="section-heading">
          <h2>Explore CertPath</h2>
          <p>Choose the lane that matches the next outcome you want.</p>
        </div>
        <div class="cards-grid">
          <article class="card explore-card paid">
            <span class="badge paid">Paid certifications</span>
            <h3>Prepare for major exams</h3>
            <p class="muted">Keep one professional certification primary and drive it with lessons, mocks, projects, and readiness checks.</p>
            <a href="{{ route('dashboard.page', ['dashboardPage' => 'roadmap']) }}">Open paid lane</a>
          </article>
          <article class="card explore-card free">
            <span class="badge free">Free credentials</span>
            <h3>Earn continuous badges</h3>
            <p class="muted">Run up to two active free credentials beside the paid track for steady supporting momentum.</p>
            <a href="{{ route('dashboard.page', ['dashboardPage' => 'roadmap']) }}">Open free lane</a>
          </article>
          <article class="card explore-card">
            <span class="badge free">Specialisations</span>
            <h3>Build supporting skills</h3>
            <p class="muted">Use projects, resources, flashcards, and notes to connect GIS, data, knowledge systems, and analytics practice.</p>
            <a href="{{ route('dashboard.page', ['dashboardPage' => 'projects']) }}">Open projects</a>
          </article>
        </div>
      </section>
      @endif

      @if ($dashboardPage === 'catalogue')
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
              <option value="skill_specialisation" @selected(old('track_type') === 'skill_specialisation')>Skill specialisation</option>
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
      @endif

      @if ($dashboardPage === 'planner')
      <section id="planner" class="content">
        @php
          $todayPlannedMinutes = $todaySessions->sum('planned_minutes');
          $todayCompletedMinutes = $todaySessions->where('status', 'completed')->sum('actual_minutes');
          $weekPlannedMinutes = $weekSessions->sum('planned_minutes');
          $weeklyTarget = $user?->profile?->weekly_target_minutes ?? 0;
          $nextSession = $todaySessions->first() ?? $studySessions->first();
          $continueUrl = $nextSession?->lesson
            ? route('certifications.show', ['certificationSlug' => $nextSession->certification->slug, 'workspacePage' => 'lesson'])
            : ($primary ? route('certifications.show', ['certificationSlug' => $primary->slug, 'workspacePage' => 'lesson']) : route('dashboard.page', ['dashboardPage' => 'planner']));
        @endphp

        <div class="section-heading">
          <h2>Study planner</h2>
          <p>Today, timetable, goals, milestones, and next-best recommendations in one place.</p>
        </div>

        <div class="planner-board">
          <article class="panel planner-today">
            <p class="eyebrow">Today</p>
            <h2>{{ now()->format('l, F j') }}</h2>
            @if ($nextSession)
              <p><strong>{{ $nextSession->certification?->exam_code }}</strong> - {{ $nextSession->target_description ?: $nextSession->activity_type }}</p>
              <p class="muted">{{ ($nextSession->scheduled_start ?? $nextSession->scheduled_for)->format('H:i') }} to {{ optional($nextSession->scheduled_end)->format('H:i') ?? 'open' }} / {{ $nextSession->planned_minutes }} min</p>
            @else
              <p>No session is scheduled yet. Create a measurable block and continue from here.</p>
            @endif
            <div class="goal-ring-row">
              <div class="goal-ring"><strong>{{ $todayCompletedMinutes }}</strong><span>/ {{ max($todayPlannedMinutes, 1) }} min</span></div>
              <div class="goal-ring"><strong>{{ $todaySessions->count() }}</strong><span>today blocks</span></div>
              <div class="goal-ring"><strong>{{ $studyStreak?->current_streak ?? 0 }}</strong><span>day streak</span></div>
            </div>
            <a class="primary-action" href="{{ $continueUrl }}">Continue today's plan</a>
          </article>

          <aside class="panel planner-summary">
            <h3>Weekly workload</h3>
            <div class="progress-block">
              <div class="progress-line">
                <span>{{ $weekPlannedMinutes }} / {{ max($weeklyTarget, 1) }} minutes planned</span>
                <strong>{{ $weeklyTarget > 0 ? min(100, (int) round(($weekPlannedMinutes / $weeklyTarget) * 100)) : 0 }}%</strong>
              </div>
              <div class="progress-track"><span style="width: {{ $weeklyTarget > 0 ? min(100, (int) round(($weekPlannedMinutes / $weeklyTarget) * 100)) : 0 }}%"></span></div>
            </div>
            <p class="muted">Default balance target: 70% primary paid, 20% supporting free, 10% project or skill.</p>
          </aside>
        </div>

        <div class="planner-grid planner-main-grid">
          <div class="planner-stack">
            <article class="panel">
              <h3>Generated plans</h3>
              <div class="session-list">
                @forelse ($studyPlans as $plan)
                  <article class="session-item">
                    <strong>{{ $plan->name }}</strong>
                    <span>{{ $plan->starts_on->format('M j') }} - {{ $plan->ends_on->format('M j, Y') }} / {{ $plan->sessions_count }} sessions</span>
                    <p>{{ ucfirst($plan->status) }} / generated by {{ $plan->generated_by }}</p>
                  </article>
                @empty
                  <p class="muted">No generated plans yet.</p>
                @endforelse
              </div>
            </article>

            <article class="panel">
              <h3>Timetable</h3>
              <div class="session-list">
                @forelse ($studySessions as $session)
                  <article class="session-item planner-session">
                    <div>
                      <strong>{{ $session->certification?->exam_code }} - {{ $session->activity_type }}</strong>
                      <span>{{ ($session->scheduled_start ?? $session->scheduled_for)->format('M j, H:i') }} / {{ $session->planned_minutes }} min / priority {{ $session->priority }}</span>
                      @if ($session->topic)
                        <p>{{ $session->topic->name }}</p>
                      @endif
                      @if ($session->target_description)
                        <p>{{ $session->target_description }}</p>
                      @endif
                      @foreach ($session->tasks as $task)
                        <p class="task-line">{{ $task->title }} - {{ $task->status }}</p>
                      @endforeach
                    </div>
                    <form method="POST" action="{{ route('study-sessions.complete', ['studySession' => $session->id]) }}">
                      @csrf
                      <input type="hidden" name="actual_minutes" value="{{ $session->planned_minutes }}">
                      <button type="submit" class="secondary-action">Complete</button>
                    </form>
                  </article>
                @empty
                  <p class="muted">No sessions scheduled yet.</p>
                @endforelse
              </div>
            </article>

            <article class="panel">
              <h3>Goals</h3>
              <div class="goal-list">
                @forelse ($studyGoals as $goal)
                  <div class="goal-item">
                    <div class="progress-line">
                      <span>{{ str_replace('_', ' ', $goal->goal_type) }} - {{ $goal->certification?->exam_code ?? 'All tracks' }}</span>
                      <strong>{{ $goal->current_value }} / {{ $goal->target_value }} {{ $goal->unit }}</strong>
                    </div>
                    <div class="progress-track"><span style="width: {{ min(100, (int) round(($goal->current_value / max($goal->target_value, 1)) * 100)) }}%"></span></div>
                    <p class="muted">{{ ucfirst($goal->goal_period) }} goal, due {{ $goal->ends_on->format('M j') }}</p>
                  </div>
                @empty
                  <p class="muted">No active goals yet.</p>
                @endforelse
              </div>
            </article>

            <article class="panel">
              <h3>Project milestones</h3>
              <div class="session-list">
                @forelse ($projectMilestones as $milestone)
                  <article class="session-item">
                    <strong>{{ $milestone->project?->certification?->exam_code }} - {{ $milestone->title }}</strong>
                    <span>{{ $milestone->target_date?->format('M j, Y') ?? 'No target date' }}</span>
                    <p>{{ $milestone->description }}</p>
                  </article>
                @empty
                  <p class="muted">No project milestones yet.</p>
                @endforelse
              </div>
            </article>
          </div>

          <aside class="planner-stack">
            <article class="panel">
              <h3>Availability</h3>
              <div class="session-list">
                @php($dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'])
                @forelse ($weeklyAvailabilities as $slot)
                  <article class="session-item">
                    <strong>{{ $dayNames[$slot->day_of_week] ?? 'Day '.$slot->day_of_week }}</strong>
                    <span>{{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }} / {{ $slot->is_active ? 'active' : 'inactive' }}</span>
                  </article>
                @empty
                  <p class="muted">No availability slots yet.</p>
                @endforelse
              </div>
            </article>

            <form method="POST" action="{{ route('weekly-availabilities.store') }}" class="panel study-form">
              @csrf
              <h3>Add availability</h3>
              <label for="availability-day">
                Day
                <select id="availability-day" name="day_of_week" required>
                  @foreach ([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'] as $dayNumber => $dayName)
                    <option value="{{ $dayNumber }}">{{ $dayName }}</option>
                  @endforeach
                </select>
              </label>
              <label for="availability-start">
                Start
                <input id="availability-start" name="start_time" type="time" value="19:00" required>
              </label>
              <label for="availability-end">
                End
                <input id="availability-end" name="end_time" type="time" value="20:00" required>
              </label>
              <label class="checkbox-row" for="availability-active">
                <input id="availability-active" name="is_active" type="checkbox" value="1" checked>
                Active
              </label>
              <button type="submit" class="primary-action">Save availability</button>
            </form>

            <form method="POST" action="{{ route('study-plans.generate') }}" class="panel study-form">
              @csrf
              <h3>Generate plan</h3>
              <label for="plan-starts">
                Week starts
                <input id="plan-starts" name="starts_on" type="date" value="{{ now()->startOfWeek()->toDateString() }}" required>
              </label>
              <label for="plan-weeks">
                Weeks
                <input id="plan-weeks" name="weeks" type="number" min="1" max="4" value="1">
              </label>
              <button type="submit" class="primary-action">Generate from availability</button>
            </form>

            <form method="POST" action="{{ route('study-sessions.store') }}" class="panel study-form">
              @csrf
              <h3>Schedule session</h3>
              <label for="session-certification">
                Certification
                <select id="session-certification" name="certification_id" required>
                  @foreach ($certifications as $certification)
                    <option value="{{ $certification->id }}">{{ $certification->exam_code }}: {{ $certification->name }}</option>
                  @endforeach
                </select>
              </label>
              <label for="session-lesson">
                Lesson
                <select id="session-lesson" name="lesson_id">
                  <option value="">No specific lesson</option>
                  @foreach ($certifications as $certification)
                    @foreach ($certification->lessons as $lesson)
                      <option value="{{ $lesson->id }}">{{ $certification->exam_code }} - {{ $lesson->title }}</option>
                    @endforeach
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
                Starts
                <input id="session-scheduled-for" name="scheduled_for" type="datetime-local" required>
              </label>
              <label for="session-minutes">
                Minutes
                <input id="session-minutes" name="planned_minutes" type="number" min="5" max="480" value="45" required>
              </label>
              <label for="session-priority">
                Priority
                <input id="session-priority" name="priority" type="number" min="1" max="5" value="3">
              </label>
              <label for="session-target">
                Measurable target
                <textarea id="session-target" name="target_description" rows="3" placeholder="Complete one lesson and answer 10 questions."></textarea>
              </label>
              <label for="session-notes">
                Notes
                <textarea id="session-notes" name="notes" rows="3"></textarea>
              </label>
              <button type="submit" class="primary-action">Schedule</button>
            </form>

            <form method="POST" action="{{ route('study-goals.store') }}" class="panel study-form">
              @csrf
              <h3>Add goal</h3>
              <label for="goal-certification">
                Certification
                <select id="goal-certification" name="certification_id">
                  <option value="">All active learning</option>
                  @foreach ($certifications as $certification)
                    <option value="{{ $certification->id }}">{{ $certification->exam_code }}: {{ $certification->name }}</option>
                  @endforeach
                </select>
              </label>
              <label for="goal-period">
                Period
                <select id="goal-period" name="goal_period" required>
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                  <option value="certification">Certification</option>
                </select>
              </label>
              <label for="goal-type">
                Goal type
                <select id="goal-type" name="goal_type" required>
                  <option value="study_minutes">Study minutes</option>
                  <option value="lessons_completed">Lessons completed</option>
                  <option value="questions_answered">Questions answered</option>
                  <option value="flashcards_reviewed">Flashcards reviewed</option>
                  <option value="project_tasks_completed">Project tasks completed</option>
                  <option value="readiness_target">Readiness target</option>
                </select>
              </label>
              <label for="goal-target">
                Target
                <input id="goal-target" name="target_value" type="number" min="1" value="60" required>
              </label>
              <label for="goal-unit">
                Unit
                <input id="goal-unit" name="unit" type="text" value="minutes" required>
              </label>
              <label for="goal-starts">
                Starts
                <input id="goal-starts" name="starts_on" type="date" value="{{ now()->toDateString() }}" required>
              </label>
              <label for="goal-ends">
                Ends
                <input id="goal-ends" name="ends_on" type="date" value="{{ now()->endOfWeek()->toDateString() }}" required>
              </label>
              <button type="submit" class="primary-action">Add goal</button>
            </form>

            <article class="panel">
              <h3>Recommendations</h3>
              <div class="session-list">
                @forelse ($plannerRecommendations as $recommendation)
                  <article class="session-item">
                    <strong>{{ str_replace('_', ' ', $recommendation->recommendation_type) }}</strong>
                    <span>{{ $recommendation->certification?->exam_code ?? 'All tracks' }} / {{ $recommendation->duration_minutes ?? 0 }} min</span>
                    <p>{{ $recommendation->reason }}</p>
                  </article>
                @empty
                  <p class="muted">No planner recommendations yet.</p>
                @endforelse
              </div>
            </article>
          </aside>
        </div>
      </section>
      @endif

      @if ($dashboardPage === 'roadmap')
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
                <h3><a href="{{ route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'overview']) }}">{{ $certification->exam_code }}: {{ $certification->name }}</a></h3>
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
                <h3><a href="{{ route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'overview']) }}">{{ $certification->exam_code }}: {{ $certification->name }}</a></h3>
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
          <div class="lane">
            <h3>Skill specialisations</h3>
            @foreach ($certifications->where('track_type.value', 'skill_specialisation') as $certification)
              <article class="card lane-item">
                <span class="badge free">Skill specialisation</span>
                <h3><a href="{{ route('certifications.show', ['certificationSlug' => $certification->slug, 'workspacePage' => 'overview']) }}">{{ $certification->exam_code }}: {{ $certification->name }}</a></h3>
                <p class="muted">{{ $certification->status }} - target {{ optional($certification->target_completion_date)->format('M j, Y') ?? 'not set' }}</p>
              </article>
            @endforeach
          </div>
        </div>
      </section>
      @endif

      @if ($dashboardPage === 'workspace')
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
      @endif

      @if ($dashboardPage === 'projects')
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
      @endif

      @if ($dashboardPage === 'specialisations')
      <section id="specialisations" class="content">
        <div class="section-heading">
          <h2>Specialisations</h2>
          <p>GIS, knowledge systems, search, datasets, analytics, and portfolio infrastructure from the amendment.</p>
        </div>

        <div class="cards-grid">
          @forelse ($specialisations as $specialisation)
            <article class="card">
              <span class="badge free">Priority {{ $specialisation->priority }}</span>
              <h3>{{ $specialisation->name }}</h3>
              <p>{{ $specialisation->description }}</p>
              <p class="muted">
                {{ $specialisation->certifications->pluck('exam_code')->filter()->join(', ') ?: 'No linked certifications yet' }}
              </p>
            </article>
          @empty
            <p class="muted">No specialisations seeded yet.</p>
          @endforelse
        </div>

        <div class="dashboard-grid specialisation-grid">
          <article class="panel">
            <h3>Datasets</h3>
            <div class="resource-list">
              @forelse ($datasets as $dataset)
                <article class="resource-row">
                  <div>
                    <strong>
                      @if ($dataset->source_url)
                        <a href="{{ $dataset->source_url }}" target="_blank" rel="noreferrer">{{ $dataset->name }}</a>
                      @else
                        {{ $dataset->name }}
                      @endif
                    </strong>
                    <p class="muted">{{ $dataset->dataset_type }} / {{ $dataset->specialisation?->name }} / {{ $dataset->certification?->exam_code }}</p>
                    <p>{{ $dataset->description }}</p>
                  </div>
                  <span class="badge free">{{ $dataset->licence ?? 'licence pending' }}</span>
                </article>
              @empty
                <p class="muted">No datasets tracked yet.</p>
              @endforelse
            </div>
          </article>

          <article class="panel">
            <h3>Ontology resources</h3>
            <div class="session-list">
              @forelse ($ontologyResources as $resource)
                <article class="session-item">
                  <strong>{{ $resource->name }}</strong>
                  <span>{{ $resource->resource_type }} / {{ $resource->specialisation?->name }}</span>
                  @if ($resource->source_url)
                    <p><a href="{{ $resource->source_url }}" target="_blank" rel="noreferrer">{{ $resource->source_url }}</a></p>
                  @endif
                  <p class="muted">{{ $resource->namespace_uri }}</p>
                </article>
              @empty
                <p class="muted">No ontology resources tracked yet.</p>
              @endforelse
            </div>
          </article>
        </div>

        <div class="dashboard-grid specialisation-grid">
          <article class="panel">
            <h3>Search lab</h3>
            <div class="session-list">
              @forelse ($searchIndexes as $index)
                <article class="session-item">
                  <strong>{{ $index->engine }}: {{ $index->index_name }}</strong>
                  <span>{{ $index->status }} / {{ $index->document_count }} documents</span>
                  <p class="muted">{{ $index->project?->title }}</p>
                </article>
              @empty
                <p class="muted">No search indexes tracked yet.</p>
              @endforelse
            </div>
          </article>

          <article class="panel">
            <h3>Analytics properties</h3>
            <div class="session-list">
              @forelse ($analyticsProperties as $property)
                <article class="session-item">
                  <strong>{{ $property->provider }}: {{ $property->property_name }}</strong>
                  <span>{{ $property->status }}</span>
                  <p class="muted">{{ $property->project?->title }}</p>
                </article>
              @empty
                <p class="muted">No analytics properties tracked yet.</p>
              @endforelse
            </div>
          </article>
        </div>
      </section>
      @endif

      @if ($dashboardPage === 'resources')
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
      @endif
    </main>
  </div>
</x-layouts.app>
