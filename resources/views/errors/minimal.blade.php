<x-layouts.app :title="$title ?? 'CertPath 123'">
  <main class="error-shell">
    <section class="error-card" role="alert">
      <a class="brand-mark" href="{{ route('dashboard') }}">CertPath 123</a>
      <span class="error-code">{{ $code ?? 'Error' }}</span>
      <h1>{{ $heading ?? 'Something needs attention' }}</h1>
      <p>{{ $message ?? 'The app could not complete that request. Your study data is still safe.' }}</p>
      <div class="error-actions">
        <a class="primary-action" href="{{ route('dashboard.page', ['dashboardPage' => 'learn']) }}">Back to learning</a>
        <a class="secondary-action" href="{{ route('dashboard.page', ['dashboardPage' => 'planner']) }}">Open planner</a>
      </div>
    </section>
  </main>
</x-layouts.app>
