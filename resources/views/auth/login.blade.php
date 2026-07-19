<x-layouts.app title="Sign in - CertPath 123">
  <main class="auth-shell">
    <section class="auth-card">
      <p class="eyebrow">Private learning system</p>
      <h1>Sign in to CertPath 123</h1>
      <p class="muted">Use your local learner account to continue studying.</p>

      @if ($errors->any())
        <div class="form-error" role="alert">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf
        <label for="email">
          Email
          <input id="email" name="email" type="email" value="{{ old('email', 'learner@certpath.test') }}" autocomplete="email" required autofocus>
        </label>

        <label for="password">
          Password
          <input id="password" name="password" type="password" autocomplete="current-password" required>
        </label>

        <label class="checkbox-row" for="remember">
          <input id="remember" name="remember" type="checkbox" value="1">
          Remember this device
        </label>

        <button type="submit" class="primary-action">Sign in</button>
      </form>

      <p class="muted">Seed account: learner@certpath.test / password</p>
    </section>
  </main>
</x-layouts.app>
