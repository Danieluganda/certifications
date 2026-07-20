@include('errors.minimal', [
    'code' => '403',
    'title' => 'Access limited',
    'heading' => 'This area is not available for your account',
    'message' => 'You are signed in, but this page or action is not available to you.',
])
